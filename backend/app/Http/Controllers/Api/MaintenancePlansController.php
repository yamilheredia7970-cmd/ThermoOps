<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMaintenancePlanRequest;
use App\Http\Requests\UpdateMaintenancePlanRequest;
use App\Http\Resources\MaintenancePlanResource;
use App\Models\MaintenancePlan;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class MaintenancePlansController extends Controller
{
    public function __construct(private readonly ActivityLogger $activity) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', MaintenancePlan::class);

        $plans = MaintenancePlan::query()
            ->with('customer:id,name')
            ->withCount('equipment')
            ->orderBy('next_service')
            ->paginate($request->integer('per_page', 25));

        return MaintenancePlanResource::collection($plans);
    }

    public function store(StoreMaintenancePlanRequest $request): JsonResponse
    {
        $data = $request->validated();
        $equipmentIds = $data['equipment_ids'] ?? [];
        unset($data['equipment_ids']);

        $plan = MaintenancePlan::create([
            ...$data,
            'status' => $data['status'] ?? 'Pending',
        ]);
        $plan->equipment()->sync($equipmentIds);
        $plan->load('customer:id,name');

        $this->activity->log(
            'Customer',
            'Maintenance Scheduled',
            "{$plan->plan_name} scheduled for {$plan->customer->name}, next service {$plan->next_service->toFormattedDateString()}.",
            $plan,
            $request->user(),
        );

        return (new MaintenancePlanResource($plan->loadCount('equipment')))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(MaintenancePlan $maintenancePlan): MaintenancePlanResource
    {
        $this->authorize('view', $maintenancePlan);

        return new MaintenancePlanResource($maintenancePlan->load('customer:id,name')->loadCount('equipment'));
    }

    public function update(UpdateMaintenancePlanRequest $request, MaintenancePlan $maintenancePlan): MaintenancePlanResource
    {
        $data = $request->validated();

        if (array_key_exists('equipment_ids', $data)) {
            $maintenancePlan->equipment()->sync($data['equipment_ids']);
            unset($data['equipment_ids']);
        }

        $maintenancePlan->update($data);

        return new MaintenancePlanResource($maintenancePlan->load('customer:id,name')->loadCount('equipment'));
    }

    public function destroy(MaintenancePlan $maintenancePlan): Response
    {
        $this->authorize('delete', $maintenancePlan);

        $maintenancePlan->delete();

        return response()->noContent();
    }
}
