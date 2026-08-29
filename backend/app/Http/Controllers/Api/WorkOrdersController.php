<?php

namespace App\Http\Controllers\Api;

use App\Events\WorkOrderSaved;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWorkOrderRequest;
use App\Http\Requests\UpdateWorkOrderRequest;
use App\Http\Resources\WorkOrderResource;
use App\Models\User;
use App\Models\WorkOrder;
use App\Notifications\WorkOrderAssigned;
use App\Services\ActivityLogger;
use App\Services\InventoryLedger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class WorkOrdersController extends Controller
{
    private const EAGER_LOAD = ['customer:id,name', 'location:id,name', 'equipment:id,brand,type', 'technician:id,name'];

    public function __construct(
        private readonly InventoryLedger $ledger,
        private readonly ActivityLogger $activity,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', WorkOrder::class);

        $workOrders = WorkOrder::query()
            ->with(self::EAGER_LOAD)
            ->when(
                ! $request->user()->hasAnyRole(['Admin', 'Dispatcher']),
                fn ($query) => $query->where('technician_id', $request->user()->id)
            )
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('technician_id'), fn ($query) => $query->where('technician_id', $request->integer('technician_id')))
            ->when($request->filled('customer_id'), fn ($query) => $query->where('customer_id', $request->integer('customer_id')))
            ->when($request->filled('equipment_id'), fn ($query) => $query->where('equipment_id', $request->integer('equipment_id')))
            ->when($request->filled('date'), fn ($query) => $query->whereDate('scheduled_at', $request->date('date')))
            ->orderBy('scheduled_at')
            ->paginate($request->integer('per_page', 25));

        return WorkOrderResource::collection($workOrders);
    }

    public function store(StoreWorkOrderRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;
        $data['status'] ??= 'Scheduled';
        $data['priority'] ??= 'Normal';

        $workOrder = WorkOrder::create($data);
        $workOrder->load([...self::EAGER_LOAD, 'lineItems']);

        WorkOrderSaved::dispatch($workOrder);

        $assignee = $workOrder->technician_id
            ? "dispatched to {$workOrder->technician->name}"
            : 'created unassigned';
        $this->activity->log(
            'WorkOrder',
            'New Work Order Dispatched',
            "WO-{$workOrder->id} {$assignee} for {$workOrder->customer->name}.",
            $workOrder,
            $request->user(),
        );

        if ($workOrder->technician) {
            $workOrder->technician->notify(new WorkOrderAssigned($workOrder));
        }

        return (new WorkOrderResource($workOrder))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(WorkOrder $workOrder): WorkOrderResource
    {
        $this->authorize('view', $workOrder);

        return new WorkOrderResource($workOrder->load([...self::EAGER_LOAD, 'lineItems']));
    }

    public function update(UpdateWorkOrderRequest $request, WorkOrder $workOrder): WorkOrderResource
    {
        $data = $request->validated();
        $previousStatus = $workOrder->status;
        $previousTechnicianId = $workOrder->technician_id;

        $workOrder->fill($data);
        $newStatus = $workOrder->status;

        if ($previousStatus !== 'Completed' && $newStatus === 'Completed') {
            $workOrder->completed_at = now();
        } elseif ($newStatus !== 'Completed') {
            $workOrder->completed_at = null;
        }

        $workOrder->save();

        if ($previousStatus !== 'Completed' && $newStatus === 'Completed') {
            $this->consumeReservedParts($workOrder, $request->user());
        } elseif ($previousStatus !== 'Cancelled' && $newStatus === 'Cancelled') {
            $this->releaseReservedParts($workOrder, $request->user());
        }

        $workOrder->load([...self::EAGER_LOAD, 'lineItems']);
        WorkOrderSaved::dispatch($workOrder);

        $this->logStatusTransition($workOrder, $previousStatus, $newStatus, $request->user());

        if ($workOrder->technician_id && $workOrder->technician_id !== $previousTechnicianId) {
            $this->activity->log(
                'WorkOrder',
                'Work Order Reassigned',
                "WO-{$workOrder->id} assigned to {$workOrder->technician->name} for {$workOrder->customer->name}.",
                $workOrder,
                $request->user(),
            );
            $workOrder->technician->notify(new WorkOrderAssigned($workOrder));
        }

        return new WorkOrderResource($workOrder);
    }

    public function destroy(Request $request, WorkOrder $workOrder): Response
    {
        $this->authorize('delete', $workOrder);

        if (! in_array($workOrder->status, ['Completed', 'Cancelled'], true)) {
            $this->releaseReservedParts($workOrder, $request->user());
        }

        $workOrder->delete();

        return response()->noContent();
    }

    private function logStatusTransition(WorkOrder $workOrder, string $previousStatus, string $newStatus, User $actor): void
    {
        if ($previousStatus === $newStatus) {
            return;
        }

        $messages = [
            'In Progress' => ['title' => 'Work Order Started', 'verb' => 'started'],
            'Completed' => ['title' => 'Work Order Completed', 'verb' => 'completed'],
            'Cancelled' => ['title' => 'Work Order Cancelled', 'verb' => 'cancelled'],
        ];

        if (! isset($messages[$newStatus])) {
            return;
        }

        $this->activity->log(
            'WorkOrder',
            $messages[$newStatus]['title'],
            "{$actor->name} marked WO-{$workOrder->id} as {$messages[$newStatus]['verb']}.",
            $workOrder,
            $actor,
        );
    }

    private function consumeReservedParts(WorkOrder $workOrder, User $actor): void
    {
        foreach ($workOrder->lineItems()->where('type', 'part')->get() as $lineItem) {
            $this->ledger->consume($lineItem->inventoryItem, (int) $lineItem->quantity, $workOrder, $actor);
        }
    }

    private function releaseReservedParts(WorkOrder $workOrder, User $actor): void
    {
        foreach ($workOrder->lineItems()->where('type', 'part')->get() as $lineItem) {
            $this->ledger->release($lineItem->inventoryItem, (int) $lineItem->quantity, $workOrder, $actor);
        }
    }
}
