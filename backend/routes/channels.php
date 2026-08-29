<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/**
 * A user's own private channel, used automatically by Laravel's
 * Notification broadcasting (the "broadcast" channel on a Notification).
 */
Broadcast::channel('App.Models.User.{id}', function (User $user, int $id) {
    return $user->id === $id;
});

/**
 * Company-wide operational events (work order created/updated, low stock,
 * technician status changes). Admin/Dispatcher only - the same audience as
 * the Dashboard endpoint.
 */
Broadcast::channel('dispatch-board', function (User $user) {
    return $user->hasAnyRole(['Admin', 'Dispatcher']);
});

/**
 * A single technician's own assignments and status. Staff can listen in
 * (e.g. to reflect a technician's live status on the dispatch board); the
 * technician can always listen to their own channel.
 */
Broadcast::channel('technician.{id}', function (User $user, int $id) {
    return $user->hasAnyRole(['Admin', 'Dispatcher']) || $user->id === $id;
});
