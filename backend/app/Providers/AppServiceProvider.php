<?php

namespace App\Providers;

use App\Models\Customer;
use App\Models\Equipment;
use App\Models\MaintenancePlan;
use App\Models\ServiceReport;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /**
         * Not enforced: enforceMorphMap() rejects any morph type outside
         * this list, which would break Spatie's roles/permissions pivots
         * and Sanctum's personal_access_tokens (both key on
         * "App\Models\User", and User isn't - and shouldn't be - listed
         * here). Plain morphMap() aliases only the types below and leaves
         * everything else on its default FQCN.
         */
        Relation::morphMap([
            'WorkOrder' => WorkOrder::class,
            'Equipment' => Equipment::class,
            'Customer' => Customer::class,
            'MaintenancePlan' => MaintenancePlan::class,
            'ServiceReport' => ServiceReport::class,
        ]);
    }
}
