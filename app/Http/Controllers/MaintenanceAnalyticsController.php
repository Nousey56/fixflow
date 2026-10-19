<?php

namespace App\Http\Controllers;

use App\Models\CompanyMembership;
use App\Models\MaintenanceRequest;
use App\Models\Property;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class MaintenanceAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Only active company administrators and managers
        // can access maintenance analytics.
        $companyIds = CompanyMembership::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->whereIn('role', ['company_admin', 'manager'])
            ->pluck('company_id');

        abort_if(
            $companyIds->isEmpty(),
            403,
            'You do not have permission to view maintenance analytics.'
        );

        // Load maintenance requests belonging only to
        // companies this user is authorized to manage.
        $requests = MaintenanceRequest::query()
            ->with([
                'unit.property',
                'assignedTechnician',
            ])
            ->whereHas('unit.property', function ($query) use ($companyIds) {
                $query->whereIn('company_id', $companyIds);
            })
            ->get();

        $total = $requests->count();
        $completed = $requests->where('status', 'completed')->count();
        $open = $requests->where('status', 'open')->count();
        $assigned = $requests->where('status', 'assigned')->count();
        $inProgress = $requests->where('status', 'in_progress')->count();

        // Overall maintenance statistics.
        $stats = [
            'total' => $total,
            'open' => $open,
            'assigned' => $assigned,
            'in_progress' => $inProgress,
            'completed' => $completed,
            'unassigned' => $requests->whereNull('assigned_to')->count(),
            'completion_rate' => $total > 0
                ? round(($completed / $total) * 100, 1)
                : 0,
        ];

        // Maintenance priority distribution.
        $priorities = [
            'emergency' => $requests->where('priority', 'emergency')->count(),
            'high' => $requests->where('priority', 'high')->count(),
            'medium' => $requests->where('priority', 'medium')->count(),
            'low' => $requests->where('priority', 'low')->count(),
        ];

        // Monthly maintenance request trends.
        // Use the last six calendar months, including the current month.
        $currentMonth = CarbonImmutable::now()->startOfMonth();

        $monthlyTrends = collect();

        for ($offset = 5; $offset >= 0; $offset--) {
            $month = $currentMonth->subMonths($offset);
            $monthEnd = $month->endOfMonth();

            $monthlyRequests = $requests->filter(function ($job) use (
                $month,
                $monthEnd
            ) {
                return $job->created_at
                    && $job->created_at->betweenIncluded(
                        $month,
                        $monthEnd
                    );
            });

            $monthlyTrends->push([
                'month' => $month->format('M Y'),
                'created' => $monthlyRequests->count(),
                'completed' => $monthlyRequests
                    ->where('status', 'completed')
                    ->count(),
            ]);
        }

        // Technician performance summary.
        $technicianPerformance = $requests
            ->whereNotNull('assigned_to')
            ->groupBy('assigned_to')
            ->map(function ($jobs) {
                $technician = $jobs->first()->assignedTechnician;

                return [
                    'technician' => $technician,
                    'total' => $jobs->count(),
                    'completed' => $jobs
                        ->where('status', 'completed')
                        ->count(),
                    'active' => $jobs
                        ->whereIn('status', [
                            'open',
                            'assigned',
                            'in_progress',
                        ])
                        ->count(),
                    'completion_rate' => $jobs->count() > 0
                        ? round(
                            ($jobs->where('status', 'completed')->count()
                                / $jobs->count()) * 100,
                            1
                        )
                        : 0,
                ];
            })
            ->sortByDesc('total')
            ->values();

        // Property maintenance insights.
        $propertyInsights = Property::query()
            ->whereIn('company_id', $companyIds)
            ->get()
            ->map(function ($property) use ($requests) {
                $propertyRequests = $requests->filter(function ($job) use (
                    $property
                ) {
                    return $job->unit
                        && $job->unit->property_id === $property->id;
                });

                return [
                    'property' => $property,
                    'total' => $propertyRequests->count(),
                    'completed' => $propertyRequests
                        ->where('status', 'completed')
                        ->count(),
                    'active' => $propertyRequests
                        ->whereIn('status', [
                            'open',
                            'assigned',
                            'in_progress',
                        ])
                        ->count(),
                    'emergency' => $propertyRequests
                        ->where('priority', 'emergency')
                        ->count(),
                ];
            })
            ->sortByDesc('total')
            ->values();

        return view('analytics.index', [
            'stats' => $stats,
            'priorities' => $priorities,
            'monthlyTrends' => $monthlyTrends,
            'technicianPerformance' => $technicianPerformance,
            'propertyInsights' => $propertyInsights,
        ]);
    }
}
