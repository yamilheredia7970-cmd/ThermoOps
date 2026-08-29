<?php

namespace App\Policies;

use App\Models\Tool;
use App\Models\User;

class ToolPolicy
{
    /**
     * Technicians can see the shared tool pool (who has what) but cannot
     * manage the catalog or reassign tools themselves.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Tool $tool): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Admin', 'Dispatcher']);
    }

    public function update(User $user, Tool $tool): bool
    {
        return $user->hasAnyRole(['Admin', 'Dispatcher']);
    }

    public function delete(User $user, Tool $tool): bool
    {
        return $user->hasRole('Admin');
    }
}
