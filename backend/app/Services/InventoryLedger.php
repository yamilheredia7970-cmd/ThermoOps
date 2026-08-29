<?php

namespace App\Services;

use App\Events\InventoryLowStock;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\DB;

/**
 * Single place that mutates InventoryItem stock counters and writes the
 * matching InventoryTransaction row, so every stock movement is auditable
 * and available_stock/reserved can never drift from the transaction log.
 */
class InventoryLedger
{
    public function reserve(InventoryItem $item, int $quantity, WorkOrder $workOrder, ?User $actor): void
    {
        DB::transaction(function () use ($item, $quantity, $workOrder, $actor) {
            $item->increment('reserved', $quantity);
            $this->recordTransaction($item, 'reserve', $quantity, $workOrder, $actor);
        });
    }

    public function release(InventoryItem $item, int $quantity, ?WorkOrder $workOrder, ?User $actor): void
    {
        DB::transaction(function () use ($item, $quantity, $workOrder, $actor) {
            $item->decrement('reserved', min($quantity, $item->reserved));
            $this->recordTransaction($item, 'release', $quantity, $workOrder, $actor);
        });
    }

    public function consume(InventoryItem $item, int $quantity, WorkOrder $workOrder, ?User $actor): void
    {
        DB::transaction(function () use ($item, $quantity, $workOrder, $actor) {
            $item->decrement('reserved', min($quantity, $item->reserved));
            $item->decrement('available_stock', min($quantity, $item->available_stock));
            $this->recordTransaction($item, 'consume', $quantity, $workOrder, $actor);
        });

        $this->notifyIfLow($item);
    }

    public function restock(InventoryItem $item, int $quantity, ?User $actor): void
    {
        DB::transaction(function () use ($item, $quantity, $actor) {
            $item->increment('available_stock', $quantity);
            $this->recordTransaction($item, 'restock', $quantity, null, $actor);
        });
    }

    private function notifyIfLow(InventoryItem $item): void
    {
        $item->refresh();

        if ($item->stockStatus() !== 'In Stock') {
            InventoryLowStock::dispatch($item);
        }
    }

    private function recordTransaction(InventoryItem $item, string $type, int $quantity, ?WorkOrder $workOrder, ?User $actor): void
    {
        InventoryTransaction::create([
            'inventory_item_id' => $item->id,
            'work_order_id' => $workOrder?->id,
            'type' => $type,
            'quantity' => $quantity,
            'performed_by' => $actor?->id,
            'occurred_at' => now(),
        ]);
    }
}
