<?php

namespace App\Policies;

use App\Models\InventoryItem;
use App\Models\User;

class InventoryItemPolicy
{
    /**
     * Technicians can see stock levels (they need to know what's available
     * for a job) but cannot manage the catalog.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, InventoryItem $inventoryItem): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Admin', 'Dispatcher']);
    }

    public function update(User $user, InventoryItem $inventoryItem): bool
    {
        return $user->hasAnyRole(['Admin', 'Dispatcher']);
    }

    public function delete(User $user, InventoryItem $inventoryItem): bool
    {
        return $user->hasRole('Admin');
    }
}
