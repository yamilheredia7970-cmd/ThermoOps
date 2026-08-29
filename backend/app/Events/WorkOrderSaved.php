<?php

namespace App\Events;

use App\Http\Resources\WorkOrderResource;
use App\Models\WorkOrder;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired whenever a work order is created or updated. Broadcast synchronously
 * (not queued): this environment has no queue worker running by default, and
 * a dispatch-board update that only appears after someone starts one isn't
 * "real-time".
 */
class WorkOrderSaved implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public WorkOrder $workOrder) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [new PrivateChannel('dispatch-board')];

        if ($this->workOrder->technician_id) {
            $channels[] = new PrivateChannel('technician.'.$this->workOrder->technician_id);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'work-order.saved';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        $this->workOrder->loadMissing(['customer:id,name', 'location:id,name', 'equipment:id,brand,type', 'technician:id,name']);

        return (new WorkOrderResource($this->workOrder))->resolve();
    }
}
