<?php

namespace App\Notifications;

use App\Models\MaintenancePlan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class MaintenanceDueSoon extends Notification
{
    use Queueable;

    public function __construct(public MaintenancePlan $plan) {}

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
            'title' => "Maintenance due soon: {$this->plan->plan_name}",
            'body' => "{$this->plan->customer->name} - next service {$this->plan->next_service->toFormattedDateString()}.",
            'maintenancePlanId' => $this->plan->id,
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
