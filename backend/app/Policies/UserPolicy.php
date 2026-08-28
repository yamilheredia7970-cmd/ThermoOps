<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine whether the user can view the list of employee accounts.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        return $user->hasRole('Admin') || $user->is($model);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        return $user->hasRole('Admin') || $user->is($model);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->hasRole('Admin') && ! $user->is($model);
    }

    /**
     * Determine whether the user can browse the operational technician
     * roster (used by dispatch to assign work).
     */
    public function viewAnyTechnicians(User $user): bool
    {
        return $user->hasAnyRole(['Admin', 'Dispatcher']);
    }

    /**
     * Determine whether the user can view the operational technician
     * profile (skills, availability, performance) of the given user.
     */
    public function viewTechnicianProfile(User $user, User $technician): bool
    {
        return $user->hasAnyRole(['Admin', 'Dispatcher']) || $user->is($technician);
    }
}
