<?php

namespace App\Services;

use App\Events\ActivityLogged;
use App\Models\Activity;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Single place that writes to the activity feed and broadcasts it live, so
 * every logging call site doesn't have to remember to do both.
 */
class ActivityLogger
{
    public function log(string $type, string $title, string $description, ?Model $subject = null, ?User $actor = null): Activity
    {
        $activity = Activity::create([
            'type' => $type,
            'title' => $title,
            'description' => $description,
            'actor_id' => $actor?->id,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'occurred_at' => now(),
        ]);

        ActivityLogged::dispatch($activity);

        return $activity;
    }
}
