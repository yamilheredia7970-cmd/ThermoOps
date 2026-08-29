<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWorkOrderRequest;
use App\Http\Requests\UpdateWorkOrderRequest;
use App\Http\Resources\WorkOrderResource;
use App\Models\User;
use App\Models\WorkOrder;
use App\Services\InventoryLedger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class WorkOrdersController extends Controller
{
    private const EAGER_LOAD = ['customer:id,name', 'location:id,name', 'equipment:id,brand,type', 'technician:id,name'];

    public function __construct(private readonly InventoryLedger $ledger) {}

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

        return (new WorkOrderResource($workOrder->load([...self::EAGER_LOAD, 'lineItems'])))
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

        return new WorkOrderResource($workOrder->load([...self::EAGER_LOAD, 'lineItems']));
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
