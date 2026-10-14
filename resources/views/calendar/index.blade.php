@php
    $weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    $statusLabels = ['open' => 'Open', 'assigned' => 'Assigned', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];
    $priorityLabels = ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'emergency' => 'Emergency'];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Calendar | FixFlow</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #f5f5f7; color: #1d1d1f; font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Text', 'Helvetica Neue', Arial, sans-serif; -webkit-font-smoothing: antialiased; }
        a { color: #0071e3; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .page { max-width: 1280px; margin: auto; padding: 48px 24px 90px; }
        .eyebrow { color: #0071e3; font-size: 12px; font-weight: 750; letter-spacing: 1.2px; text-transform: uppercase; }
        .header { display: flex; justify-content: space-between; align-items: end; gap: 20px; margin-bottom: 28px; flex-wrap: wrap; }
        h1 { font-size: clamp(36px, 5vw, 56px); letter-spacing: -2px; line-height: 1.1; margin: 10px 0; }
        .subtitle { margin: 0; color: #6e6e73; line-height: 1.5; }
        .button { display: inline-flex; align-items: center; justify-content: center; padding: 12px 18px; border-radius: 999px; background: #0071e3; color: #fff; font-size: 14px; font-weight: 650; white-space: nowrap; }
        .button:hover { background: #0077ed; text-decoration: none; }
        .button.secondary { background: #e8e8ed; color: #1d1d1f; }
        .button.secondary:hover { background: #dedee3; }
        .stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 15px; margin-bottom: 24px; }
        .stat { background: white; border: 1px solid #e8e8ed; border-radius: 22px; padding: 22px; }
        .stat-label { font-size: 12px; font-weight: 750; letter-spacing: .5px; text-transform: uppercase; color: #6e6e73; }
        .stat-number { font-size: 38px; font-weight: 750; letter-spacing: -1px; margin-top: 8px; }
        .stat-note { font-size: 12px; color: #86868b; margin-top: 4px; }
        .panel { background: #fff; border: 1px solid #e8e8ed; border-radius: 24px; padding: 24px; }
        .calendar-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 22px; }
        .calendar-toolbar h2 { font-size: 26px; letter-spacing: -.8px; margin: 0; }
        .month-controls { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
        .calendar-scroll { overflow-x: auto; }
        .calendar { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); min-width: 760px; border-top: 1px solid #e8e8ed; border-left: 1px solid #e8e8ed; border-radius: 12px; overflow: hidden; }
        .weekday { background: #f5f5f7; text-align: center; font-size: 12px; font-weight: 750; color: #6e6e73; padding: 13px 6px; border-right: 1px solid #e8e8ed; border-bottom: 1px solid #e8e8ed; }
        .day { min-height: 138px; padding: 10px 8px; border-right: 1px solid #e8e8ed; border-bottom: 1px solid #e8e8ed; min-width: 0; }
        .day.outside { background: #fafafa; }
        .day-number { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 50%; font-size: 13px; font-weight: 700; margin-bottom: 7px; }
        .outside .day-number { color: #a1a1a6; }
        .day.today .day-number { background: #0071e3; color: white; }
        .appointment { display: block; padding: 7px 8px; margin-bottom: 6px; border-radius: 9px; background: #eef5ff; color: #174c8a; border-left: 3px solid #0071e3; font-size: 11px; line-height: 1.35; overflow-wrap: anywhere; }
        .appointment:hover { background: #dcecff; text-decoration: none; }
        .appointment.priority-high { border-left-color: #ff453a; background: #fff0ee; color: #a62c23; }
        .appointment.priority-emergency { border-left-color: #b42318; background: #ffe2df; color: #8e2018; }
        .appointment.priority-medium { border-left-color: #e5a100; background: #fff7e2; color: #8b6100; }
        .appointment.completed { opacity: .65; }
        .appointment-title { display: block; font-weight: 750; }
        .appointment-meta { display: block; margin-top: 3px; opacity: .85; }
        .legend { display: flex; flex-wrap: wrap; gap: 18px; margin-top: 20px; font-size: 12px; color: #6e6e73; }
        .legend-item { display: inline-flex; align-items: center; gap: 7px; }
        .legend-dot { width: 10px; height: 10px; border-radius: 50%; background: #0071e3; }
        .legend-dot.medium { background: #e5a100; }
        .legend-dot.high { background: #ff453a; }
        .legend-dot.emergency { background: #b42318; }
        .note { margin: 14px 0 0; color: #86868b; font-size: 12px; line-height: 1.6; }
        .empty { text-align: center; padding: 24px; color: #6e6e73; font-size: 14px; }
        @media (max-width: 800px) { .stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } .page { padding: 32px 15px 70px; } .panel { padding: 16px; } }
        @media (max-width: 420px) { .stat { padding: 16px; } .stat-number { font-size: 30px; } }
    </style>
</head>
<body>
    <x-fixflow-nav />
    <main class="page">
        <header class="header">
            <div>
                <div class="eyebrow">FixFlow Enterprise</div>
                <h1>Maintenance Calendar.</h1>
                <p class="subtitle">See scheduled work orders and technician assignments at a glance.</p>
            </div>
            <a class="button secondary" href="{{ route('dispatch.index') }}">Back to Dispatch →</a>
        </header>

        <section class="stats" aria-label="Monthly appointment statistics">
            <div class="stat"><div class="stat-label">Appointments</div><div class="stat-number">{{ number_format($stats['total'] ?? 0) }}</div><div class="stat-note">Work orders overlapping this month</div></div>
            <div class="stat"><div class="stat-label">Active</div><div class="stat-number">{{ number_format($stats['scheduled'] ?? 0) }}</div><div class="stat-note">Not completed or cancelled</div></div>
            <div class="stat"><div class="stat-label">Completed</div><div class="stat-number">{{ number_format($stats['completed'] ?? 0) }}</div><div class="stat-note">Scheduled work orders completed</div></div>
            <div class="stat"><div class="stat-label">Unassigned</div><div class="stat-number">{{ number_format($stats['unassigned'] ?? 0) }}</div><div class="stat-note">No technician assigned</div></div>
        </section>

        <section class="panel" aria-label="Monthly maintenance calendar">
            <div class="calendar-toolbar">
                <h2>{{ $month->format('F Y') }}</h2>
                <nav class="month-controls" aria-label="Change calendar month">
                    <a class="button secondary" href="{{ route('calendar.index', ['month' => $previousMonth]) }}" aria-label="Previous month">← Previous</a>
                    <a class="button secondary" href="{{ route('calendar.index') }}">Current Month</a>
                    <a class="button secondary" href="{{ route('calendar.index', ['month' => $nextMonth]) }}" aria-label="Next month">Next →</a>
                </nav>
            </div>

            <div class="calendar-scroll">
                <div class="calendar">
                    @foreach ($weekdays as $weekday)
                        <div class="weekday">{{ $weekday }}</div>
                    @endforeach

                    @foreach ($days as $day)
                        @php
                            $date = $day['date'];
                            $dateKey = $date->format('Y-m-d');
                            $dayAppointments = $appointmentsByDate[$dateKey] ?? [];
                        @endphp
                        <div class="day {{ $day['is_current_month'] ? '' : 'outside' }} {{ $day['is_today'] ? 'today' : '' }}">
                            <span class="day-number" aria-label="{{ $date->format('F j, Y') }}">{{ $date->day }}</span>
                            @foreach ($dayAppointments as $appointment)
                                <a class="appointment priority-{{ $appointment->priority }} {{ $appointment->status === 'completed' ? 'completed' : '' }}"
                                   href="{{ route('maintenance.show', $appointment) }}"
                                   title="{{ $appointment->title }} — {{ $appointment->assignedTechnician?->name ?? 'Unassigned' }}">
                                    <span class="appointment-title">#{{ $appointment->id }} {{ $appointment->title }}</span>
                                    <span class="appointment-meta">{{ $appointment->unit?->property?->name ?? 'Unknown property' }} · Unit {{ $appointment->unit?->unit_number ?? '—' }}</span>
                                    <span class="appointment-meta">{{ $appointment->assignedTechnician?->name ?? 'Unassigned' }}</span>
                                    <span class="appointment-meta">{{ $statusLabels[$appointment->status] ?? ucfirst($appointment->status) }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="legend" aria-label="Priority legend">
                <span class="legend-item"><span class="legend-dot"></span>Low</span>
                <span class="legend-item"><span class="legend-dot medium"></span>Medium</span>
                <span class="legend-item"><span class="legend-dot high"></span>High</span>
                <span class="legend-item"><span class="legend-dot emergency"></span>Emergency</span>
            </div>
            <p class="note">Multi-day appointments appear on each scheduled day. Select any appointment to open its work order. On smaller screens, scroll the calendar horizontally.</p>
            @if ($appointments->isEmpty())
                <p class="empty">No scheduled work orders overlap {{ $month->format('F Y') }}. You can schedule appointments from a work order.</p>
            @endif
        </section>
    </main>
</body>
</html>
