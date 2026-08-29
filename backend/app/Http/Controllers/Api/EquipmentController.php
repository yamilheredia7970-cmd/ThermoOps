<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEquipmentRequest;
use App\Http\Requests\UpdateEquipmentRequest;
use App\Http\Resources\EquipmentResource;
use App\Models\Equipment;
use App\Models\Location;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class EquipmentController extends Controller
{
    public function __construct(private readonly ActivityLogger $activity) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Equipment::class);

        $equipment = Equipment::query()
            ->with('location:id,name')
            ->when(
                $request->integer('customer_id'),
                fn ($query, $customerId) => $query->where('customer_id', $customerId)
            )
            ->when(
                $request->integer('location_id'),
                fn ($query, $locationId) => $query->where('location_id', $locationId)
            )
            ->orderBy('type')
            ->paginate($request->integer('per_page', 25));

        return EquipmentResource::collection($equipment);
    }

    public function store(StoreEquipmentRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['customer_id'] = Location::findOrFail($data['location_id'])->customer_id;

        $equipment = Equipment::create($data);

        return (new EquipmentResource($equipment->load('location:id,name')))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Equipment $equipment): EquipmentResource
    {
        $this->authorize('view', $equipment);

        return new EquipmentResource($equipment->load('location:id,name'));
    }

    public function update(UpdateEquipmentRequest $request, Equipment $equipment): EquipmentResource
    {
        $data = $request->validated();
        $previousStatus = $equipment->status;

        if (array_key_exists('location_id', $data)) {
            $data['customer_id'] = Location::findOrFail($data['location_id'])->customer_id;
        }

        $equipment->update($data);

        if (array_key_exists('status', $data) && $data['status'] !== $previousStatus) {
            $this->activity->log(
                'Equipment',
                'Equipment Status Changed',
                "{$equipment->brand} {$equipment->type} status changed from {$previousStatus} to {$equipment->status}.",
                $equipment,
                $request->user(),
            );
        }

        return new EquipmentResource($equipment->load('location:id,name'));
    }

    public function destroy(Equipment $equipment): Response
    {
        $this->authorize('delete', $equipment);

        $equipment->delete();

        return response()->noContent();
    }
}
