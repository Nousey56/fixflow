<?php

namespace App\Http\Controllers;

use App\Models\CompanyMembership;
use App\Models\MaintenanceRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TechnicianWorkloadController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Only active administrators and managers can view workloads.
        $companyIds = CompanyMembership::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->whereIn('role', ['company_admin', 'manager', 'demo_viewer'])
            ->pluck('company_id');

        abort_if(
            $companyIds->isEmpty(),
            403,
            'You do not have permission to view technician workloads.'
        );

        // Get technicians belonging to companies this manager can access.
        $technicianIds = CompanyMembership::query()
            ->whereIn('company_id', $companyIds)
            ->where('status', 'active')
            ->where('role', 'technician')
            ->distinct()
            ->pluck('user_id');

        $technicians = User::query()
            ->whereIn('id', $technicianIds)
            ->orderBy('name')
            ->get();

        // Count work orders only within authorized companies.
        $workOrderCounts = MaintenanceRequest::query()
            ->select([
                'assigned_to',
                'status',
                DB::raw('COUNT(*) as total'),
            ])
            ->whereNotNull('assigned_to')
            ->whereHas('unit.property', function ($query) use ($companyIds) {
                $query->whereIn('company_id', $companyIds);
            })
            ->whereIn('assigned_to', $technicianIds)
            ->groupBy('assigned_to', 'status')
            ->get()
            ->groupBy('assigned_to');

        // Get the next scheduled appointment for each technician.
        $upcomingAppointments = MaintenanceRequest::query()
            ->with(['unit.property'])
            ->whereNotNull('assigned_to')
            ->whereNotNull('scheduled_start_at')
            ->where('scheduled_start_at', '>=', now())
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->whereIn('assigned_to', $technicianIds)
            ->whereHas('unit.property', function ($query) use ($companyIds) {
                $query->whereIn('company_id', $companyIds);
            })
            ->orderBy('scheduled_start_at')
            ->get()
            ->groupBy('assigned_to');

        $workloads = $technicians->map(function ($technician) use (
            $workOrderCounts,
            $upcomingAppointments
        ) {
            $counts = $workOrderCounts->get(
                $technician->id,
                collect()
            );

            $countFor = fn (string $status) => (int) $counts
                ->where('status', $status)
                ->sum('total');

            $open = $countFor('open');
            $assigned = $countFor('assigned');
            $inProgress = $countFor('in_progress');
            $completed = $countFor('completed');

            $active = $open + $assigned + $inProgress;

            // Initial workload thresholds based on active job count.
            $workloadLevel = match (true) {
                $active >= 8 => 'heavy',
                $active >= 4 => 'moderate',
                default => 'light',
            };

            return [
                'technician' => $technician,
                'open' => $open,
                'assigned' => $assigned,
                'in_progress' => $inProgress,
                'completed' => $completed,
                'active' => $active,
                'total' => $counts->sum('total'),
                'workload_level' => $workloadLevel,
                'next_appointment' => $upcomingAppointments
                    ->get($technician->id, collect())
                    ->first(),
            ];
        });

        $workloads = $workloads
            ->sortByDesc('active')
            ->values();

        $stats = [
            'technicians' => $technicians->count(),
            'active_jobs' => $workloads->sum('active'),
            'completed_jobs' => $workloads->sum('completed'),
            'heavy_workloads' => $workloads
                ->where('workload_level', 'heavy')
                ->count(),
            'unassigned_jobs' => MaintenanceRequest::query()
                ->whereNull('assigned_to')
                ->whereIn('status', [
                    'open',
                    'assigned',
                    'in_progress',
                ])
                ->whereHas('unit.property', function ($query) use ($companyIds) {
                    $query->whereIn('company_id', $companyIds);
                })
                ->count(),
        ];

        return view('workload.index', [
            'workloads' => $workloads,
            'stats' => $stats,
        ]);
    }
}
