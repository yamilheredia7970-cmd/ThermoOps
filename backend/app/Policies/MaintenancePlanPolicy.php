<?php

namespace App\Policies;

use App\Models\MaintenancePlan;
use App\Models\User;

class MaintenancePlanPolicy
{
    /**
     * Planning/contract data is a dispatch concern, same as Customer/
     * Location/Equipment: no technician access yet.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Admin', 'Dispatcher']);
    }

    public function view(User $user, MaintenancePlan $maintenancePlan): bool
    {
        return $user->hasAnyRole(['Admin', 'Dispatcher']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Admin', 'Dispatcher']);
    }

    public function update(User $user, MaintenancePlan $maintenancePlan): bool
    {
        return $user->hasAnyRole(['Admin', 'Dispatcher']);
    }

    public function delete(User $user, MaintenancePlan $maintenancePlan): bool
    {
        return $user->hasRole('Admin');
    }
}
