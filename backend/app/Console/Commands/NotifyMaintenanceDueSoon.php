<?php

namespace App\Console\Commands;

use App\Models\MaintenancePlan;
use App\Models\User;
use App\Notifications\MaintenanceDueSoon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

/**
 * Fires exactly once per plan: it only matches plans whose next_service is
 * precisely 7 days out, rather than "within 7 days", so a daily run can't
 * re-notify the same plan on consecutive days.
 */
#[Signature('app:notify-maintenance-due-soon')]
#[Description('Notify Admin/Dispatcher users about maintenance plans due in 7 days')]
class NotifyMaintenanceDueSoon extends Command
{
    private const DAYS_AHEAD = 7;

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $plans = MaintenancePlan::query()
            ->whereIn('status', ['Active', 'Pending'])
            ->whereDate('next_service', now()->addDays(self::DAYS_AHEAD)->toDateString())
            ->with('customer:id,name')
            ->get();

        if ($plans->isEmpty()) {
            $this->info('No maintenance plans due in '.self::DAYS_AHEAD.' days.');

            return;
        }

        $staff = User::role(['Admin', 'Dispatcher'])->get();

        foreach ($plans as $plan) {
            Notification::send($staff, new MaintenanceDueSoon($plan));
        }

        $this->info("Notified {$staff->count()} staff member(s) about {$plans->count()} plan(s) due soon.");
    }
}
