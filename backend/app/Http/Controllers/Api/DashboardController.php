<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WorkOrderResource;
use App\Models\TechnicianProfile;
use App\Models\WorkOrder;
use App\Models\WorkOrderLineItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Billing and company-wide staffing numbers are Admin/Dispatcher
     * business data; a Technician has no dashboard of their own yet.
     */
    public function index(Request $request): JsonResponse
    {
        abort_unless($request->user()->hasAnyRole(['Admin', 'Dispatcher']), 403);

        return response()->json([
            'data' => [
                'activeWorkOrders' => WorkOrder::active()->count(),
                'technicians' => $this->technicianAvailability(),
                'monthlyRevenue' => $this->monthlyRevenue(),
                'serviceActivity' => $this->serviceActivityForLastSevenDays(),
                'todaysSchedule' => WorkOrderResource::collection($this->todaysSchedule()),
            ],
        ]);
    }

    /**
     * @return array{available: int, total: int, inField: int}
     */
    private function technicianAvailability(): array
    {
        $statuses = TechnicianProfile::query()->pluck('availability_status');

        return [
            'available' => $statuses->filter(fn ($status) => $status === 'Available')->count(),
            'total' => $statuses->count(),
            'inField' => $statuses->filter(fn ($status) => in_array($status, ['On Site', 'In Transit'], true))->count(),
        ];
    }

    private function monthlyRevenue(): float
    {
        $total = WorkOrderLineItem::query()
            ->whereHas('workOrder', fn ($query) => $query->whereBetween(
                'completed_at',
                [now()->startOfMonth(), now()->endOfMonth()]
            ))
            ->get()
            ->sum(fn (WorkOrderLineItem $lineItem) => $lineItem->subtotal());

        return round($total, 2);
    }

    /**
     * @return array<int, array{date: string, day: string, completed: int, scheduled: int}>
     */
    private function serviceActivityForLastSevenDays(): array
    {
        $since = today()->subDays(6);

        $completedByDay = WorkOrder::query()
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', $since)
            ->selectRaw('DATE(completed_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $scheduledByDay = WorkOrder::query()
            ->where('scheduled_at', '>=', $since)
            ->selectRaw('DATE(scheduled_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        return collect(range(6, 0))
            ->map(function (int $daysAgo) use ($completedByDay, $scheduledByDay) {
                $date = today()->subDays($daysAgo);
                $key = $date->toDateString();

                return [
                    'date' => $key,
                    'day' => $date->format('D'),
                    'completed' => (int) ($completedByDay[$key] ?? 0),
                    'scheduled' => (int) ($scheduledByDay[$key] ?? 0),
                ];
            })
            ->values()
            ->all();
    }

    private function todaysSchedule(): Collection
    {
        return WorkOrder::query()
            ->with(['customer:id,name', 'location:id,name', 'technician:id,name'])
            ->whereDate('scheduled_at', today())
            ->orderBy('scheduled_at')
            ->get();
    }
}
