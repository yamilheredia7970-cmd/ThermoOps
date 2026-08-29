<?php

namespace App\Events;

use App\Models\Activity;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ActivityLogged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Activity $activity) {}

    /**
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('dispatch-board')];
    }

    public function broadcastAs(): string
    {
        return 'activity.logged';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id' => (string) $this->activity->id,
            'type' => $this->activity->type,
            'title' => $this->activity->title,
            'description' => $this->activity->description,
            'timestamp' => $this->activity->occurred_at->toIso8601String(),
            'relatedId' => $this->activity->subject_id !== null ? (string) $this->activity->subject_id : null,
            'actor' => $this->activity->actor?->name ?? 'System',
        ];
    }
}
