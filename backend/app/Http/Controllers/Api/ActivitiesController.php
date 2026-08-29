<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityResource;
use App\Models\Activity;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ActivitiesController extends Controller
{
    /**
     * Admin/Dispatcher get the full feed. Everyone else must scope to a
     * specific subject or technician, and only sees activity tied to their
     * own work orders regardless of what they ask for (there is no
     * general-purpose "activity" ability to check).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $isStaff = $user->hasAnyRole(['Admin', 'Dispatcher']);

        abort_unless($isStaff || $request->filled('subject_type') || $request->filled('technician_id'), 403);

        $activities = Activity::query()
            ->with('actor:id,name')
            ->when($request->filled('subject_type'), fn ($query) => $query->where('subject_type', $request->string('subject_type')))
            ->when($request->filled('subject_id'), fn ($query) => $query->where('subject_id', $request->integer('subject_id')))
            ->when(
                $request->filled('technician_id'),
                fn ($query) => $query->whereHasMorph(
                    'subject',
                    [WorkOrder::class],
                    fn ($subQuery) => $subQuery->where('technician_id', $request->integer('technician_id'))
                )
            )
            ->when(
                ! $isStaff,
                fn ($query) => $query->whereHasMorph(
                    'subject',
                    [WorkOrder::class],
                    fn ($subQuery) => $subQuery->where('technician_id', $user->id)
                )
            )
            ->orderByDesc('occurred_at')
            ->paginate($request->integer('per_page', 20));

        return ActivityResource::collection($activities);
    }
}
