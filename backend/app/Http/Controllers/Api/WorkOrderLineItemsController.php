<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWorkOrderLineItemRequest;
use App\Http\Resources\WorkOrderLineItemResource;
use App\Models\InventoryItem;
use App\Models\WorkOrder;
use App\Models\WorkOrderLineItem;
use App\Services\InventoryLedger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WorkOrderLineItemsController extends Controller
{
    public function __construct(private readonly InventoryLedger $ledger) {}

    public function store(StoreWorkOrderLineItemRequest $request, WorkOrder $workOrder): JsonResponse
    {
        $data = $request->validated();

        if ($data['type'] === 'part') {
            $item = InventoryItem::findOrFail($data['inventory_item_id']);
            $data['unit_price'] ??= $item->unit_cost;
        }

        $lineItem = $workOrder->lineItems()->create($data);

        if ($lineItem->type === 'part') {
            $this->ledger->reserve($lineItem->inventoryItem, (int) $lineItem->quantity, $workOrder, $request->user());
        }

        return (new WorkOrderLineItemResource($lineItem->load('inventoryItem')))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function destroy(Request $request, WorkOrder $workOrder, WorkOrderLineItem $lineItem): Response
    {
        $this->authorize('manageLineItems', $workOrder);

        if ($lineItem->type === 'part') {
            $this->ledger->release($lineItem->inventoryItem, (int) $lineItem->quantity, $workOrder, $request->user());
        }

        $lineItem->delete();

        return response()->noContent();
    }
}
