<?php

namespace App\Notifications;

use App\Models\WorkOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

/**
 * Not queued: this environment has no queue worker running by default, and
 * an assignment notice that only appears once someone starts one isn't
 * "real-time".
 */
class WorkOrderAssigned extends Notification
{
    use Queueable;

    public function __construct(public WorkOrder $workOrder) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => "New work order assigned: WO-{$this->workOrder->id}",
            'body' => "{$this->workOrder->service_type} at {$this->workOrder->customer->name}, scheduled {$this->workOrder->scheduled_at->format('M j, g:i A')}.",
            'workOrderId' => $this->workOrder->id,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
