<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInventoryItemRequest;
use App\Http\Requests\UpdateInventoryItemRequest;
use App\Http\Resources\InventoryItemResource;
use App\Models\InventoryItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class InventoryItemsController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', InventoryItem::class);

        $items = InventoryItem::query()
            ->orderBy('part_name')
            ->paginate($request->integer('per_page', 25));

        return InventoryItemResource::collection($items);
    }

    public function store(StoreInventoryItemRequest $request): JsonResponse
    {
        $item = InventoryItem::create($request->validated());

        return (new InventoryItemResource($item))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(InventoryItem $inventoryItem): InventoryItemResource
    {
        $this->authorize('view', $inventoryItem);

        return new InventoryItemResource($inventoryItem);
    }

    public function update(UpdateInventoryItemRequest $request, InventoryItem $inventoryItem): InventoryItemResource
    {
        $inventoryItem->update($request->validated());

        return new InventoryItemResource($inventoryItem);
    }

    public function destroy(InventoryItem $inventoryItem): Response
    {
        $this->authorize('delete', $inventoryItem);

        $inventoryItem->delete();

        return response()->noContent();
    }
}
