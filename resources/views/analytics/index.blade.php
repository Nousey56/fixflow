
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Analytics | FixFlow</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f5f5f7;
            color: #1d1d1f;
            font-family: -apple-system, BlinkMacSystemFont,
                "Segoe UI", sans-serif;
        }

        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 36px 24px 80px;
        }

        .eyebrow {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1.4px;
            text-transform: uppercase;
            color: #86868b;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }

        h1 {
            font-size: clamp(30px, 4vw, 42px);
            letter-spacing: -1.5px;
            margin: 8px 0;
        }

        .subtitle {
            margin: 0;
            color: #6e6e73;
            line-height: 1.6;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 18px;
            border-radius: 12px;
            background: #0071e3;
            color: white;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
        }

        .button:hover {
            background: #0077ed;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 26px;
        }

        .card {
            background: white;
            border: 1px solid #e8e8ed;
            border-radius: 20px;
            padding: 24px;
            min-width: 0;
        }

        .stat-label {
            font-size: 13px;
            color: #6e6e73;
            font-weight: 500;
        }

        .stat-value {
            font-size: 35px;
            font-weight: 750;
            letter-spacing: -1px;
            margin-top: 12px;
        }

        .stat-note {
            font-size: 12px;
            color: #86868b;
            margin-top: 8px;
        }

        .section-grid {
            display: grid;
            grid-template-columns: 1.4fr 1fr;
            gap: 20px;
            margin-bottom: 24px;
        }

        .section-title {
            font-size: 21px;
            letter-spacing: -0.5px;
            margin: 0 0 8px;
        }

        .section-description {
            font-size: 13px;
            color: #86868b;
            margin: 0 0 25px;
            line-height: 1.6;
        }

        .chart {
            display: flex;
            align-items: flex-end;
            gap: 14px;
            height: 240px;
            padding-top: 15px;
            border-bottom: 1px solid #e8e8ed;
        }

        .chart-column {
            flex: 1;
            min-width: 0;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            align-items: center;
            gap: 10px;
        }

        .chart-bars {
            display: flex;
            justify-content: center;
            align-items: flex-end;
            gap: 5px;
            width: 100%;
            flex: 1;
        }

        .chart-bar {
            width: 22px;
            max-width: 42%;
            border-radius: 6px 6px 0 0;
            min-height: 0;
            transition: height 0.2s;
        }

        .chart-bar.created {
            background: #0071e3;
        }

        .chart-bar.completed {
            background: #81b7f5;
        }

        .chart-label {
            color: #86868b;
            font-size: 11px;
            white-space: nowrap;
            margin-bottom: -25px;
        }

        .legend {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-top: 45px;
            color: #6e6e73;
            font-size: 12px;
        }

        .legend-item {
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }

        .legend-dot {
            width: 9px;
            height: 9px;
            border-radius: 3px;
        }

        .priority-row {
            margin-bottom: 21px;
        }

        .priority-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 9px;
            font-size: 13px;
            font-weight: 600;
        }

        .progress-track {
            width: 100%;
            height: 10px;
            background: #f0f0f2;
            border-radius: 999px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            border-radius: 999px;
        }

        .priority-emergency {
            background: #e45454;
        }

        .priority-high {
            background: #ef9d45;
        }

        .priority-medium {
            background: #0071e3;
        }

        .priority-low {
            background: #58b987;
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th {
            padding: 14px 12px;
            color: #86868b;
            font-size: 12px;
            font-weight: 600;
            border-bottom: 1px solid #e8e8ed;
            white-space: nowrap;
        }

        td {
            padding: 17px 12px;
            border-bottom: 1px solid #f0f0f2;
            font-size: 13px;
        }

        tr:last-child td {
            border-bottom: 0;
        }

        .name {
            font-weight: 650;
        }

        .muted {
            color: #86868b;
        }

        .rate {
            color: #0071e3;
            font-weight: 700;
        }

        .empty {
            padding: 25px 10px;
            text-align: center;
            color: #86868b;
            font-size: 13px;
        }

        .full-width {
            margin-bottom: 24px;
        }

        @media (max-width: 1000px) {
            .stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .section-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 600px) {
            .container {
                padding: 24px 16px 60px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .card {
                padding: 20px;
            }

            .chart {
                gap: 5px;
            }

            .chart-bar {
                width: 15px;
            }

            .chart-label {
                font-size: 9px;
            }
        }
    </style>
</head>

<body>

    <x-fixflow-nav />

    <main class="container">

        <header class="page-header">
            <div>
                <div class="eyebrow">FixFlow Enterprise</div>
                <h1>Maintenance Analytics</h1>
                <p class="subtitle">
                    Understand maintenance activity, team performance,
                    and property operations.
                </p>
            </div>

            <a href="{{ route('maintenance.index') }}" class="button">
                View Work Orders →
            </a>
        </header>

        <section class="stats-grid">

            <div class="card">
                <div class="stat-label">Total Requests</div>
                <div class="stat-value">{{ $stats['total'] }}</div>
                <div class="stat-note">All recorded work orders</div>
            </div>

            <div class="card">
                <div class="stat-label">Completed</div>
                <div class="stat-value">{{ $stats['completed'] }}</div>
                <div class="stat-note">Finished maintenance jobs</div>
            </div>

            <div class="card">
                <div class="stat-label">Active Requests</div>
                <div class="stat-value">
                    {{ $stats['open'] + $stats['assigned'] + $stats['in_progress'] }}
                </div>
                <div class="stat-note">Open, assigned, and in progress</div>
            </div>

            <div class="card">
                <div class="stat-label">Completion Rate</div>
                <div class="stat-value">{{ $stats['completion_rate'] }}%</div>
                <div class="stat-note">Completed ÷ total requests</div>
            </div>

        </section>

        <section class="section-grid">

            <div class="card">
                <h2 class="section-title">Monthly Maintenance Trends</h2>
                <p class="section-description">
                    Requests created over the last six months.
                    Light blue represents requests from each month
                    that are currently completed.
                </p>

                @php
                    $maxMonthly = max(
                        1,
                        $monthlyTrends->max('created') ?? 0
                    );
                @endphp

                <div class="chart">

                    @foreach($monthlyTrends as $month)
                        <div class="chart-column">

                            <div class="chart-bars">

                                <div
                                    class="chart-bar created"
                                    title="{{ $month['created'] }} created"
                                    style="height: {{ ($month['created'] / $maxMonthly) * 100 }}%"
                                ></div>

                                <div
                                    class="chart-bar completed"
                                    title="{{ $month['completed'] }} currently completed"
                                    style="height: {{ ($month['completed'] / $maxMonthly) * 100 }}%"
                                ></div>

                            </div>

                            <div class="chart-label">
                                {{ $month['month'] }}
                            </div>

                        </div>
                    @endforeach

                </div>

                <div class="legend">
                    <span class="legend-item">
                        <span class="legend-dot" style="background:#0071e3;"></span>
                        Created
                    </span>

                    <span class="legend-item">
                        <span class="legend-dot" style="background:#81b7f5;"></span>
                        Currently completed
                    </span>
                </div>
            </div>

            <div class="card">
                <h2 class="section-title">Priority Distribution</h2>
                <p class="section-description">
                    Maintenance requests grouped by urgency.
                </p>

                @foreach(['emergency', 'high', 'medium', 'low'] as $priority)

                    @php
                        $count = $priorities[$priority];
                        $percentage = $stats['total'] > 0
                            ? ($count / $stats['total']) * 100
                            : 0;
                    @endphp

                    <div class="priority-row">

                        <div class="priority-heading">
                            <span>{{ ucfirst($priority) }}</span>
                            <span>{{ $count }}</span>
                        </div>

                        <div class="progress-track">
                            <div
                                class="progress-fill priority-{{ $priority }}"
                                style="width: {{ $percentage }}%"
                            ></div>
                        </div>

                    </div>

                @endforeach
            </div>

        </section>

        <section class="card full-width">
            <h2 class="section-title">Technician Performance</h2>
            <p class="section-description">
                Work orders currently assigned to each technician.
            </p>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Technician</th>
                            <th>Total Jobs</th>
                            <th>Active</th>
                            <th>Completed</th>
                            <th>Completion Rate</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($technicianPerformance as $row)
                            <tr>
                                <td class="name">
                                    {{ $row['technician']?->name ?? 'Unknown Technician' }}
                                </td>

                                <td>{{ $row['total'] }}</td>
                                <td>{{ $row['active'] }}</td>
                                <td>{{ $row['completed'] }}</td>

                                <td class="rate">
                                    {{ $row['completion_rate'] }}%
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty">
                                    No assigned work orders available.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="card full-width">
            <h2 class="section-title">Property Insights</h2>
            <p class="section-description">
                Maintenance activity across your managed properties.
            </p>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Property</th>
                            <th>Total Requests</th>
                            <th>Active</th>
                            <th>Completed</th>
                            <th>Emergency</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($propertyInsights as $row)
                            <tr>
                                <td class="name">
                                    {{ $row['property']->name }}
                                </td>

                                <td>{{ $row['total'] }}</td>
                                <td>{{ $row['active'] }}</td>
                                <td>{{ $row['completed'] }}</td>
                                <td>{{ $row['emergency'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty">
                                    No properties available.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

    </main>

</body>
</html>
