<?php

namespace App\Http\Controllers;

use App\Models\CompanyMembership;
use App\Models\MaintenanceRequest;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class MaintenanceCalendarController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Only active administrators and managers can access the calendar.
        $companyIds = CompanyMembership::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->whereIn('role', ['company_admin', 'manager'])
            ->pluck('company_id');

        abort_if(
            $companyIds->isEmpty(),
            403,
            'You do not have permission to access the maintenance calendar.'
        );

        // Accept a month in YYYY-MM format.
        $validated = $request->validate([
            'month' => [
                'nullable',
                'date_format:Y-m',
            ],
        ]);

        $monthValue = $validated['month']
            ?? now()->format('Y-m');

        $month = CarbonImmutable::createFromFormat(
            '!Y-m',
            $monthValue
        )->startOfMonth();

        $monthStart = $month->startOfMonth();
        $monthEnd = $month->endOfMonth();

        // Include work orders whose scheduled time overlaps this month.
        // Every result must belong to an authorized company.
        $appointments = MaintenanceRequest::query()
            ->with([
                'unit.property',
                'assignedTechnician',
            ])
            ->whereHas('unit.property', function ($query) use ($companyIds) {
                $query->whereIn('company_id', $companyIds);
            })
            ->whereNotNull('scheduled_start_at')
            ->whereNotNull('scheduled_end_at')
            ->where('scheduled_start_at', '<=', $monthEnd)
            ->where('scheduled_end_at', '>=', $monthStart)
            ->orderBy('scheduled_start_at')
            ->orderBy('id')
            ->get();

        // Build a six-week calendar grid, starting on Sunday.
        $gridStart = $monthStart->startOfWeek(
            CarbonImmutable::SUNDAY
        );

        $gridEnd = $monthEnd->endOfWeek(
            CarbonImmutable::SATURDAY
        );

        $days = [];

        for (
            $day = $gridStart;
            $day->lte($gridEnd);
            $day = $day->addDay()
        ) {
            $days[] = [
                'date' => $day,
                'is_current_month' => $day->month === $month->month
                    && $day->year === $month->year,
                'is_today' => $day->isToday(),
            ];
        }

        // Group appointments by every date they occupy.
        $appointmentsByDate = [];

        foreach ($appointments as $appointment) {
            $start = CarbonImmutable::instance(
                $appointment->scheduled_start_at
            )->startOfDay();

            $end = CarbonImmutable::instance(
                $appointment->scheduled_end_at
            )->startOfDay();

            $firstDay = $start->greaterThan($gridStart)
                ? $start
                : $gridStart;

            $lastDay = $end->lessThan($gridEnd)
                ? $end
                : $gridEnd;

            for (
                $date = $firstDay;
                $date->lte($lastDay);
                $date = $date->addDay()
            ) {
                $key = $date->format('Y-m-d');

                $appointmentsByDate[$key][] = $appointment;
            }
        }

        $stats = [
            'total' => $appointments->count(),
            'scheduled' => $appointments
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->count(),
            'completed' => $appointments
                ->where('status', 'completed')
                ->count(),
            'unassigned' => $appointments
                ->whereNull('assigned_to')
                ->count(),
        ];

        return view('calendar.index', [
            'month' => $month,
            'previousMonth' => $month->subMonth()->format('Y-m'),
            'nextMonth' => $month->addMonth()->format('Y-m'),
            'days' => $days,
            'appointments' => $appointments,
            'appointmentsByDate' => $appointmentsByDate,
            'stats' => $stats,
        ]);
    }
}
