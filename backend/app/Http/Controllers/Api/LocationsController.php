<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLocationRequest;
use App\Http\Requests\UpdateLocationRequest;
use App\Http\Resources\LocationResource;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class LocationsController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Location::class);

        $locations = Location::query()
            ->with('customer:id,name')
            ->withCount('equipment')
            ->when(
                $request->integer('customer_id'),
                fn ($query, $customerId) => $query->where('customer_id', $customerId)
            )
            ->orderBy('name')
            ->paginate($request->integer('per_page', 25));

        return LocationResource::collection($locations);
    }

    public function store(StoreLocationRequest $request): JsonResponse
    {
        $location = Location::create($request->validated());

        return (new LocationResource($location->load('customer:id,name')->loadCount('equipment')))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Location $location): LocationResource
    {
        $this->authorize('view', $location);

        return new LocationResource($location->load('customer:id,name')->loadCount('equipment'));
    }

    public function update(UpdateLocationRequest $request, Location $location): LocationResource
    {
        $location->update($request->validated());

        return new LocationResource($location->load('customer:id,name')->loadCount('equipment'));
    }

    public function destroy(Location $location): Response
    {
        $this->authorize('delete', $location);

        $location->delete();

        return response()->noContent();
    }
}
