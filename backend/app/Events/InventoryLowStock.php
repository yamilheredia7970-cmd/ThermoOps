<?php

namespace App\Events;

use App\Models\InventoryItem;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InventoryLowStock implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public InventoryItem $item) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('dispatch-board')];
    }

    public function broadcastAs(): string
    {
        return 'inventory.low-stock';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->item->id,
            'partName' => $this->item->part_name,
            'availableStock' => $this->item->available_stock,
            'status' => $this->item->stockStatus(),
        ];
    }
}
