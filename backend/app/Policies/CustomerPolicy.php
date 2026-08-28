<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;

class CustomerPolicy
{
    /**
     * Technicians get no access here yet: without work orders (Phase 3) there
     * is no way to scope them to "customers they're actually assigned to".
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Admin', 'Dispatcher']);
    }

    public function view(User $user, Customer $customer): bool
    {
        return $user->hasAnyRole(['Admin', 'Dispatcher']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Admin', 'Dispatcher']);
    }

    public function update(User $user, Customer $customer): bool
    {
        return $user->hasAnyRole(['Admin', 'Dispatcher']);
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $user->hasRole('Admin');
    }
}
