<?php

namespace App\Models;

use Database\Factories\WorkOrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

#[Fillable(['customer_id', 'location_id', 'equipment_id', 'technician_id', 'created_by', 'service_type', 'status', 'priority', 'scheduled_at', 'duration_hours', 'description', 'completed_at'])]
class WorkOrder extends Model
{
    /** @use HasFactory<WorkOrderFactory> */
    use HasFactory, SoftDeletes;

    /**
     * Statuses that still occupy a technician's schedule (used for the
     * double-booking overlap check). On Hold still reserves the slot even
     * though work is paused.
     */
    public const SCHEDULE_OCCUPYING_STATUSES = ['Scheduled', 'In Progress', 'On Hold'];

    /**
     * Statuses counted as "active" for dashboard/KPI purposes, matching the
     * frontend's existing Dashboard.tsx definition.
     */
    public const ACTIVE_STATUSES = ['Scheduled', 'In Progress'];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'duration_hours' => 'decimal:2',
            'completed_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lineItems(): HasMany
    {
        return $this->hasMany(WorkOrderLineItem::class);
    }

    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->whereIn('status', self::ACTIVE_STATUSES);
    }

    public function endsAt(): Carbon
    {
        return $this->scheduled_at->clone()->addMinutes((int) round($this->duration_hours * 60));
    }

    /**
     * Whether the given technician already has a schedule-occupying work
     * order overlapping [$start, $end). Bounds the candidate set to the
     * previous 24 hours of scheduled_at, since no work order should ever
     * run that long; the exact overlap is then checked in PHP because it
     * depends on duration_hours, not a directly comparable column.
     */
    public static function hasSchedulingConflict(int $technicianId, Carbon $start, Carbon $end, ?int $excludingWorkOrderId = null): bool
    {
        return static::query()
            ->where('technician_id', $technicianId)
            ->whereIn('status', self::SCHEDULE_OCCUPYING_STATUSES)
            ->when($excludingWorkOrderId, fn (Builder $query, int $id) => $query->whereKeyNot($id))
            ->where('scheduled_at', '<', $end)
            ->where('scheduled_at', '>=', $start->clone()->subDay())
            ->get()
            ->contains(fn (self $workOrder): bool => $workOrder->endsAt()->gt($start));
    }
}
