
@php
    $total = $stats['total'] ?? 0;
    $scheduled = $stats['scheduled'] ?? 0;
    $upcoming = $stats['upcoming'] ?? 0;
    $unassigned = $stats['unassigned'] ?? 0;
    $unscheduled = $stats['unscheduled'] ?? 0;

    $view = $currentView ?? 'upcoming';

    $statusLabels = [
        'open' => 'Open',
        'assigned' => 'Assigned',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    $priorityLabels = [
        'low' => 'Low',
        'medium' => 'Medium',
        'high' => 'High',
        'emergency' => 'Emergency',
    ];

    $tabUrl = fn ($tab) => route(
        'dispatch.index',
        array_merge(request()->except('page', 'view'), ['view' => $tab])
    );
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dispatch Board | FixFlow</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f5f5f7;
            color: #1d1d1f;
            font-family: -apple-system, BlinkMacSystemFont,
                "SF Pro Text", "Helvetica Neue", Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        a {
            color: #0071e3;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .page {
            max-width: 1180px;
            margin: 0 auto;
            padding: 52px 24px 100px;
        }

        .eyebrow {
            color: #0071e3;
            font-size: 12px;
            font-weight: 750;
            letter-spacing: 1.3px;
            text-transform: uppercase;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 20px;
            margin-bottom: 34px;
        }

        h1 {
            margin: 10px 0 12px;
            font-size: clamp(36px, 5vw, 58px);
            line-height: 1.07;
            letter-spacing: -2.3px;
        }

        h2 {
            font-size: 22px;
            letter-spacing: -.6px;
            margin: 0 0 20px;
        }

        .subtitle {
            color: #6e6e73;
            font-size: 16px;
            line-height: 1.6;
            margin: 0;
        }

        .button {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            border: 0;
            border-radius: 999px;
            background: #0071e3;
            color: white;
            padding: 12px 20px;
            font-size: 14px;
            font-weight: 650;
            cursor: pointer;
            white-space: nowrap;
        }

        .button:hover {
            background: #0077ed;
            text-decoration: none;
        }

        .button-secondary {
            background: #e8e8ed;
            color: #1d1d1f;
        }

        .button-secondary:hover {
            background: #dedee3;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: white;
            border: 1px solid #e8e8ed;
            border-radius: 22px;
            padding: 24px;
            min-width: 0;
        }

        .stat-label {
            color: #6e6e73;
            font-size: 12px;
            font-weight: 750;
            letter-spacing: .5px;
            text-transform: uppercase;
        }

        .stat-value {
            font-size: 40px;
            font-weight: 750;
            letter-spacing: -1.5px;
            margin-top: 10px;
        }

        .stat-note {
            color: #86868b;
            font-size: 13px;
            margin-top: 8px;
        }

        .card {
            background: white;
            border: 1px solid #e8e8ed;
            border-radius: 24px;
            padding: 28px;
            margin-bottom: 20px;
        }

        .tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 25px;
        }

        .tab {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 11px 18px;
            background: #f5f5f7;
            color: #515154;
            font-size: 14px;
            font-weight: 650;
        }

        .tab:hover {
            background: #e8e8ed;
            text-decoration: none;
        }

        .tab.active {
            background: #1d1d1f;
            color: white;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
        }

        .field {
            min-width: 0;
        }

        .field label {
            display: block;
            font-size: 13px;
            font-weight: 650;
            margin-bottom: 9px;
        }

        .field input,
        .field select {
            width: 100%;
            min-width: 0;
            padding: 13px 14px;
            border: 1px solid #d2d2d7;
            border-radius: 13px;
            background: white;
            color: #1d1d1f;
            font: inherit;
            font-size: 14px;
        }

        .field input:focus,
        .field select:focus {
            outline: 3px solid #cce4ff;
            border-color: #0071e3;
        }

        .filter-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
            margin-top: 20px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .section-header h2 {
            margin: 0;
        }

        .results-count {
            font-size: 13px;
            color: #6e6e73;
        }

        .work-order {
            border: 1px solid #e8e8ed;
            border-radius: 20px;
            padding: 24px;
            margin-bottom: 14px;
            background: white;
        }

        .work-order:last-child {
            margin-bottom: 0;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
        }

        .order-number {
            color: #86868b;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .5px;
            text-transform: uppercase;
        }

        .order-title {
            font-size: 20px;
            font-weight: 750;
            letter-spacing: -.4px;
            margin: 7px 0 9px;
            overflow-wrap: anywhere;
        }

        .order-location {
            color: #6e6e73;
            font-size: 14px;
            line-height: 1.6;
        }

        .badges {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
            justify-content: flex-end;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 7px 11px;
            background: #f5f5f7;
            color: #515154;
            font-size: 11px;
            font-weight: 750;
            text-transform: uppercase;
            letter-spacing: .3px;
            white-space: nowrap;
        }

        .priority-low {
            background: #e8f5e9;
            color: #248a3d;
        }

        .priority-medium {
            background: #fff4d6;
            color: #9a6700;
        }

        .priority-high {
            background: #ffe5e5;
            color: #b42318;
        }

        .priority-emergency {
            background: #ff3b30;
            color: white;
        }

        .order-details {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-top: 22px;
        }

        .detail-tile {
            background: #f5f5f7;
            border-radius: 15px;
            padding: 17px;
            min-width: 0;
        }

        .detail-label {
            font-size: 11px;
            font-weight: 750;
            color: #86868b;
            letter-spacing: .5px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .detail-value {
            font-size: 14px;
            font-weight: 650;
            line-height: 1.5;
            overflow-wrap: anywhere;
        }

        .order-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            border-top: 1px solid #e8e8ed;
            padding-top: 18px;
            margin-top: 20px;
        }

        .order-created {
            color: #86868b;
            font-size: 12px;
        }

        .empty-state {
            padding: 65px 20px;
            text-align: center;
        }

        .empty-icon {
            font-size: 40px;
            margin-bottom: 16px;
        }

        .empty-state h3 {
            font-size: 22px;
            margin: 0 0 10px;
        }

        .empty-state p {
            color: #6e6e73;
            line-height: 1.6;
            margin: 0 auto 20px;
            max-width: 420px;
        }

        .pagination {
            margin-top: 25px;
        }

        .pagination nav {
            max-width: 100%;
        }

        .pagination svg {
            width: 20px;
            height: 20px;
        }

        .pagination a,
        .pagination span {
            overflow-wrap: anywhere;
        }

        .errors {
            background: #fff0f0;
            color: #b42318;
            padding: 16px 20px;
            border-radius: 14px;
            margin-bottom: 20px;
        }

        .errors ul {
            margin: 0;
            padding-left: 20px;
        }

        @media (max-width: 900px) {
            .stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .filter-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 650px) {
            .page {
                padding: 35px 16px 70px;
            }

            .page-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .stats-grid,
            .filter-grid,
            .order-details {
                grid-template-columns: 1fr;
            }

            .card {
                padding: 20px;
            }

            .work-order {
                padding: 19px;
            }

            .order-header {
                flex-direction: column;
            }

            .badges {
                justify-content: flex-start;
            }

            .stat-value {
                font-size: 34px;
            }
        }
    </style>
</head>

<body>
    <x-fixflow-nav />

    <main class="page">

        <header class="page-header">
            <div>
                <div class="eyebrow">FixFlow Enterprise</div>
                <h1>Dispatch Board.</h1>
                <p class="subtitle">
                    Schedule, organize, and track your maintenance operations.
                </p>
            </div>

            <a href="{{ route('maintenance.index') }}"
               class="button button-secondary">
                View All Requests →
            </a>
        </header>

        @if ($errors->any())
            <div class="errors" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="stats-grid" aria-label="Dispatch statistics">
            <div class="stat-card">
                <div class="stat-label">Total Work Orders</div>
                <div class="stat-value">{{ number_format($total) }}</div>
                <div class="stat-note">All recorded requests</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Scheduled</div>
                <div class="stat-value">{{ number_format($scheduled) }}</div>
                <div class="stat-note">Appointments on record</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Upcoming</div>
                <div class="stat-value">{{ number_format($upcoming) }}</div>
                <div class="stat-note">Future active appointments</div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Unassigned</div>
                <div class="stat-value">{{ number_format($unassigned) }}</div>
                <div class="stat-note">
                    {{ number_format($unscheduled) }} active jobs without a schedule
                </div>
            </div>
        </section>

        <section class="card">
            <h2>Manage dispatch</h2>

            <nav class="tabs" aria-label="Work order views">
                <a href="{{ $tabUrl('upcoming') }}"
                   class="tab {{ $view === 'upcoming' ? 'active' : '' }}"
                   @if ($view === 'upcoming') aria-current="page" @endif>
                    Upcoming
                </a>

                <a href="{{ $tabUrl('unscheduled') }}"
                   class="tab {{ $view === 'unscheduled' ? 'active' : '' }}"
                   @if ($view === 'unscheduled') aria-current="page" @endif>
                    Unscheduled
                </a>

                <a href="{{ $tabUrl('all') }}"
                   class="tab {{ $view === 'all' ? 'active' : '' }}"
                   @if ($view === 'all') aria-current="page" @endif>
                    All Work Orders
                </a>
            </nav>

            <form method="GET" action="{{ route('dispatch.index') }}">
                <input type="hidden" name="view" value="{{ $view }}">

                <div class="filter-grid">
                    <div class="field">
                        <label for="search">Search work orders</label>
                        <input
                            type="search"
                            id="search"
                            name="search"
                            value="{{ $filters['search'] ?? '' }}"
                            placeholder="Title, property, or unit..."
                        >
                    </div>

                    <div class="field">
                        <label for="technician">Technician</label>
                        <select name="technician" id="technician">
                            <option value="">All technicians</option>

                            @foreach ($technicians as $technician)
                                <option
                                    value="{{ $technician->id }}"
                                    @selected(
                                        (string) ($filters['technician'] ?? '') ===
                                        (string) $technician->id
                                    )
                                >
                                    {{ $technician->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label for="date">Scheduled date</label>
                        <input
                            type="date"
                            name="date"
                            id="date"
                            value="{{ $filters['date'] ?? '' }}"
                        >
                    </div>

                    <div class="field">
                        <label for="status">Status</label>
                        <select name="status" id="status">
                            <option value="">All statuses</option>

                            @foreach ($statusLabels as $value => $label)
                                <option
                                    value="{{ $value }}"
                                    @selected(($filters['status'] ?? '') === $value)
                                >
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label for="priority">Priority</label>
                        <select name="priority" id="priority">
                            <option value="">All priorities</option>

                            @foreach ($priorityLabels as $value => $label)
                                <option
                                    value="{{ $value }}"
                                    @selected(($filters['priority'] ?? '') === $value)
                                >
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="button">
                        Apply Filters
                    </button>

                    <a
                        href="{{ route('dispatch.index', ['view' => $view]) }}"
                        class="button button-secondary"
                    >
                        Clear Filters
                    </a>
                </div>
            </form>
        </section>

        <section class="card">
            <div class="section-header">
                <h2>
                    @if ($view === 'upcoming')
                        Upcoming appointments
                    @elseif ($view === 'unscheduled')
                        Unscheduled work orders
                    @else
                        All work orders
                    @endif
                </h2>

                <span class="results-count">
                    {{ number_format($workOrders->total()) }} results
                </span>
            </div>

            @forelse ($workOrders as $job)
                <article class="work-order">
                    <div class="order-header">
                        <div>
                            <div class="order-number">
                                Work Order #{{ $job->id }}
                            </div>

                            <div class="order-title">
                                {{ $job->title }}
                            </div>

                            <div class="order-location">
                                {{ $job->unit?->property?->name ?? 'Unknown property' }}
                                · Unit {{ $job->unit?->unit_number ?? '—' }}
                                @if ($job->unit?->floor)
                                    · Floor {{ $job->unit->floor }}
                                @endif
                            </div>
                        </div>

                        <div class="badges">
                            <span class="badge priority-{{ $job->priority }}">
                                {{ $priorityLabels[$job->priority] ?? ucfirst($job->priority) }}
                            </span>

                            <span class="badge">
                                {{ $statusLabels[$job->status] ?? ucfirst($job->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="order-details">
                        <div class="detail-tile">
                            <div class="detail-label">Scheduled Start</div>
                            <div class="detail-value">
                                {{ $job->scheduled_start_at?->format('M j, Y g:i A') ?? 'Not scheduled' }}
                            </div>
                        </div>

                        <div class="detail-tile">
                            <div class="detail-label">Scheduled End</div>
                            <div class="detail-value">
                                {{ $job->scheduled_end_at?->format('M j, Y g:i A') ?? 'Not scheduled' }}
                            </div>
                        </div>

                        <div class="detail-tile">
                            <div class="detail-label">Technician</div>
                            <div class="detail-value">
                                {{ $job->assignedTechnician?->name ?? 'Unassigned' }}
                            </div>
                        </div>
                    </div>

                    <div class="order-footer">
                        <span class="order-created">
                            Created {{ $job->created_at?->format('M j, Y') ?? 'Unknown' }}
                        </span>

                        <a
                            href="{{ route('maintenance.show', $job) }}"
                            class="button"
                        >
                            Manage Work Order →
                        </a>
                    </div>
                </article>
            @empty
                <div class="empty-state">
                    <div class="empty-icon" aria-hidden="true">📅</div>

                    <h3>No work orders found</h3>

                    <p>
                        @if ($view === 'upcoming')
                            There are no upcoming appointments matching your filters.
                            Schedule a work order to see it here.
                        @elseif ($view === 'unscheduled')
                            No unscheduled active work orders match your filters.
                        @else
                            No work orders match your current search and filters.
                        @endif
                    </p>

                    <a
                        href="{{ route('maintenance.index') }}"
                        class="button"
                    >
                        Browse Maintenance Requests →
                    </a>
                </div>
            @endforelse

            @if ($workOrders->hasPages())
                <div class="pagination">
                    {{ $workOrders->links() }}
                </div>
            @endif
        </section>

    </main>
</body>
</html>
