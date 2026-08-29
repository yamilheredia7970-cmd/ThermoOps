<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkOrder;

class WorkOrderPolicy
{
    /**
     * Everyone authenticated may list work orders; the controller scopes a
     * Technician's results to their own assignments.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, WorkOrder $workOrder): bool
    {
        return $user->hasAnyRole(['Admin', 'Dispatcher']) || $user->id === $workOrder->technician_id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Admin', 'Dispatcher']);
    }

    public function update(User $user, WorkOrder $workOrder): bool
    {
        return $user->hasAnyRole(['Admin', 'Dispatcher']) || $user->id === $workOrder->technician_id;
    }

    public function delete(User $user, WorkOrder $workOrder): bool
    {
        return $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can add/remove billing line items.
     * Closed once the work order is Completed or Cancelled.
     */
    public function manageLineItems(User $user, WorkOrder $workOrder): bool
    {
        if (in_array($workOrder->status, ['Completed', 'Cancelled'], true)) {
            return false;
        }

        return $user->hasAnyRole(['Admin', 'Dispatcher']) || $user->id === $workOrder->technician_id;
    }
}
