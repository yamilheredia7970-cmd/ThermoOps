<?php

namespace App\Policies;

use App\Models\Equipment;
use App\Models\User;

class EquipmentPolicy
{
    /**
     * Technicians get no access here yet: without work orders (Phase 3) there
     * is no way to scope them to "equipment they're actually assigned to".
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Admin', 'Dispatcher']);
    }

    public function view(User $user, Equipment $equipment): bool
    {
        return $user->hasAnyRole(['Admin', 'Dispatcher']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Admin', 'Dispatcher']);
    }

    public function update(User $user, Equipment $equipment): bool
    {
        return $user->hasAnyRole(['Admin', 'Dispatcher']);
    }

    public function delete(User $user, Equipment $equipment): bool
    {
        return $user->hasRole('Admin');
    }
}
