<?php

namespace App\Policies;

use App\Models\ServiceReport;
use App\Models\User;

class ServiceReportPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ServiceReport $serviceReport): bool
    {
        return $user->hasAnyRole(['Admin', 'Dispatcher']) || $user->id === $serviceReport->technician_id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Admin', 'Dispatcher']) || $user->hasRole('Technician');
    }

    /**
     * Signing is the only "write" a report ever gets: once created it's
     * otherwise immutable, so there's no separate update ability.
     */
    public function sign(User $user, ServiceReport $serviceReport): bool
    {
        return ($user->hasAnyRole(['Admin', 'Dispatcher']) || $user->id === $serviceReport->technician_id)
            && $serviceReport->status !== 'Signed';
    }

    public function delete(User $user, ServiceReport $serviceReport): bool
    {
        return $user->hasRole('Admin');
    }
}
