<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TechnicianResource;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TechniciansController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAnyTechnicians', User::class);

        $technicians = User::query()
            ->role('Technician')
            ->with('technicianProfile')
            ->withCount(['workOrders as jobs_today_count' => fn ($query) => $query->whereDate('scheduled_at', today())])
            ->withSum(['workOrders as hours_this_week_sum' => fn ($query) => $query->whereBetween(
                'scheduled_at',
                [now()->startOfWeek(), now()->endOfWeek()]
            )], 'duration_hours')
            ->orderBy('name')
            ->get();

        $this->attachCurrentJobIds($technicians);

        return TechnicianResource::collection($technicians);
    }

    public function show(User $technician): TechnicianResource
    {
        $this->authorize('viewTechnicianProfile', $technician);

        $technician->loadCount(['workOrders as jobs_today_count' => fn ($query) => $query->whereDate('scheduled_at', today())])
            ->loadSum(['workOrders as hours_this_week_sum' => fn ($query) => $query->whereBetween(
                'scheduled_at',
                [now()->startOfWeek(), now()->endOfWeek()]
            )], 'duration_hours')
            ->load('technicianProfile');

        $this->attachCurrentJobIds(new Collection([$technician]));

        return new TechnicianResource($technician);
    }

    /**
     * A technician has at most one "In Progress" job at a time; this fetches
     * them for the whole roster in a single query instead of one per row.
     *
     * @param  Collection<int, User>  $technicians
     */
    private function attachCurrentJobIds(Collection $technicians): void
    {
        $currentJobIds = WorkOrder::query()
            ->whereIn('technician_id', $technicians->pluck('id'))
            ->where('status', 'In Progress')
            ->get(['id', 'technician_id', 'scheduled_at'])
            ->groupBy('technician_id')
            ->map(fn (Collection $jobs) => $jobs->sortByDesc('scheduled_at')->first()->id);

        $technicians->each(function (User $technician) use ($currentJobIds) {
            $technician->current_job_id = $currentJobIds->get($technician->id);
        });
    }
}
