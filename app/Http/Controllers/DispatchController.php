<?php

namespace App\Http\Controllers;

use App\Models\CompanyMembership;
use App\Models\MaintenanceRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DispatchController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Only active company administrators and managers
        // can access the dispatch board.
        $companyIds = CompanyMembership::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->whereIn('role', ['company_admin', 'manager'])
            ->pluck('company_id');

        abort_if(
            $companyIds->isEmpty(),
            403,
            'You do not have permission to access dispatch.'
        );

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => [
                'nullable',
                'in:open,assigned,in_progress,completed,cancelled',
            ],
            'priority' => [
                'nullable',
                'in:low,medium,high,emergency',
            ],
            'technician' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'date' => [
                'nullable',
                'date_format:Y-m-d',
            ],
            'view' => [
                'nullable',
                'in:upcoming,all,unscheduled',
            ],
        ]);

        $view = $validated['view'] ?? 'upcoming';

        $baseQuery = MaintenanceRequest::query()
            ->whereHas('unit.property', function ($query) use ($companyIds) {
                $query->whereIn('company_id', $companyIds);
            });

        // Summary statistics are calculated before filters.
        $stats = [
            'total' => (clone $baseQuery)->count(),

            'scheduled' => (clone $baseQuery)
                ->whereNotNull('scheduled_start_at')
                ->count(),

            'unscheduled' => (clone $baseQuery)
                ->whereNull('scheduled_start_at')
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->count(),

            'upcoming' => (clone $baseQuery)
                ->whereNotNull('scheduled_start_at')
                ->where('scheduled_start_at', '>=', Carbon::now())
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->count(),

            'unassigned' => (clone $baseQuery)
                ->whereNull('assigned_to')
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->count(),
        ];

        $query = clone $baseQuery;

        if ($view === 'upcoming') {
            $query->whereNotNull('scheduled_start_at')
                ->where('scheduled_start_at', '>=', Carbon::now())
                ->whereNotIn('status', ['completed', 'cancelled']);
        }

        if ($view === 'unscheduled') {
            $query->whereNull('scheduled_start_at')
                ->whereNotIn('status', ['completed', 'cancelled']);
        }

        if (!empty($validated['search'])) {
            $search = trim($validated['search']);

            $query->where(function ($builder) use ($search) {
                $builder->where('title', 'like', '%' . $search . '%')
                    ->orWhereHas('unit', function ($unitQuery) use ($search) {
                        $unitQuery->where('unit_number', 'like', '%' . $search . '%')
                            ->orWhereHas('property', function ($propertyQuery) use ($search) {
                                $propertyQuery->where('name', 'like', '%' . $search . '%');
                            });
                    });
            });
        }

        if (!empty($validated['status'])) {
            $query->where('status', $validated['status']);
        }

        if (!empty($validated['priority'])) {
            $query->where('priority', $validated['priority']);
        }

        if (!empty($validated['technician'])) {
            $query->where('assigned_to', $validated['technician']);
        }

        if (!empty($validated['date'])) {
            $query->whereDate(
                'scheduled_start_at',
                $validated['date']
            );
        }

        $workOrders = $query
            ->with([
                'unit.property',
                'assignedTechnician',
            ])
            ->orderByRaw(
                'CASE WHEN scheduled_start_at IS NULL THEN 1 ELSE 0 END'
            )
            ->orderBy('scheduled_start_at')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        // Only list technicians belonging to authorized companies.
        $technicianIds = CompanyMembership::query()
            ->whereIn('company_id', $companyIds)
            ->where('status', 'active')
            ->where('role', 'technician')
            ->pluck('user_id')
            ->unique();

        $technicians = User::query()
            ->whereIn('id', $technicianIds)
            ->orderBy('name')
            ->get();

        return view('dispatch.index', [
            'workOrders' => $workOrders,
            'technicians' => $technicians,
            'stats' => $stats,
            'filters' => $validated,
            'currentView' => $view,
        ]);
    }
}
