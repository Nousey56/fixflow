
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Technician Workload | FixFlow</title>

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

        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 30px;
        }

        .eyebrow {
            color: #86868b;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        h1 {
            font-size: clamp(30px, 4vw, 42px);
            letter-spacing: -1.5px;
            margin: 8px 0;
        }

        .subtitle {
            color: #6e6e73;
            margin: 0;
            line-height: 1.6;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 20px;
            background: #0071e3;
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
        }

        .button:hover {
            background: #0077ed;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 30px;
        }

        .stat-card,
        .technician-card,
        .empty-state {
            background: white;
            border: 1px solid #e8e8ed;
            border-radius: 20px;
        }

        .stat-card {
            padding: 22px;
        }

        .stat-label {
            color: #6e6e73;
            font-size: 13px;
            font-weight: 500;
        }

        .stat-value {
            font-size: 34px;
            font-weight: 750;
            letter-spacing: -1px;
            margin-top: 12px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 18px;
        }

        .section-header h2 {
            font-size: 23px;
            letter-spacing: -0.5px;
            margin: 0;
        }

        .section-note {
            color: #86868b;
            font-size: 13px;
        }

        .technician-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }

        .technician-card {
            padding: 26px;
        }

        .technician-header {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .avatar {
            width: 48px;
            height: 48px;
            flex-shrink: 0;
            display: grid;
            place-items: center;
            background: #eaf3ff;
            color: #0071e3;
            border-radius: 15px;
            font-size: 19px;
            font-weight: 700;
        }

        .technician-name {
            font-size: 18px;
            font-weight: 700;
            margin: 0 0 4px;
        }

        .technician-email {
            color: #86868b;
            font-size: 13px;
            overflow-wrap: anywhere;
        }

        .workload-badge {
            margin-left: auto;
            padding: 7px 11px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            white-space: nowrap;
        }

        .light {
            background: #e8f7ee;
            color: #21834a;
        }

        .moderate {
            background: #fff4df;
            color: #9a6500;
        }

        .heavy {
            background: #ffebeb;
            color: #c83737;
        }

        .workload-summary {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-top: 28px;
            margin-bottom: 12px;
        }

        .active-number {
            font-size: 32px;
            font-weight: 750;
            letter-spacing: -1px;
        }

        .muted {
            color: #86868b;
            font-size: 13px;
        }

        .progress-track {
            height: 9px;
            background: #f0f0f2;
            border-radius: 999px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: #0071e3;
            border-radius: 999px;
        }

        .progress-fill.moderate {
            background: #e6a23c;
        }

        .progress-fill.heavy {
            background: #e45454;
        }

        .job-counts {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            margin-top: 25px;
        }

        .job-count {
            background: #f5f5f7;
            border-radius: 12px;
            padding: 14px 10px;
            text-align: center;
        }

        .job-count strong {
            display: block;
            font-size: 22px;
            margin-bottom: 5px;
        }

        .job-count span {
            color: #6e6e73;
            font-size: 11px;
        }

        .appointment {
            margin-top: 24px;
            padding: 18px;
            border: 1px solid #e8e8ed;
            border-radius: 14px;
        }

        .appointment-title {
            font-size: 12px;
            font-weight: 700;
            color: #86868b;
            text-transform: uppercase;
            letter-spacing: 0.7px;
            margin-bottom: 9px;
        }

        .appointment a {
            color: #0071e3;
            text-decoration: none;
            font-weight: 650;
        }

        .appointment-meta {
            margin-top: 8px;
            color: #6e6e73;
            font-size: 13px;
            line-height: 1.6;
        }

        .empty-state {
            padding: 60px 25px;
            text-align: center;
        }

        .empty-state h3 {
            font-size: 22px;
            margin-bottom: 8px;
        }

        .empty-state p {
            color: #6e6e73;
            line-height: 1.6;
        }

        @media (max-width: 1050px) {
            .stats-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }

        @media (max-width: 760px) {
            .stats-grid,
            .technician-grid {
                grid-template-columns: 1fr;
            }

            .container {
                padding: 24px 16px 60px;
            }

            .technician-card {
                padding: 20px;
            }
        }

        @media (max-width: 430px) {
            .job-counts {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .technician-header {
                flex-wrap: wrap;
            }

            .workload-badge {
                margin-left: 0;
            }
        }
    </style>
</head>

<body>

    <x-fixflow-nav />

    <main class="container">

        <div class="page-header">
            <div>
                <div class="eyebrow">FixFlow Enterprise</div>
                <h1>Technician Workload</h1>
                <p class="subtitle">
                    Monitor assignments, balance workloads,
                    and keep maintenance operations moving.
                </p>
            </div>

            <a href="{{ route('dispatch.index') }}" class="button">
                Open Dispatch Board →
            </a>
        </div>

        <section class="stats-grid">

            <div class="stat-card">
                <div class="stat-label">Technicians</div>
                <div class="stat-value">
                    {{ $stats['technicians'] }}
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Active Jobs</div>
                <div class="stat-value">
                    {{ $stats['active_jobs'] }}
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Completed Jobs</div>
                <div class="stat-value">
                    {{ $stats['completed_jobs'] }}
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Heavy Workloads</div>
                <div class="stat-value">
                    {{ $stats['heavy_workloads'] }}
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Unassigned Jobs</div>
                <div class="stat-value">
                    {{ $stats['unassigned_jobs'] }}
                </div>
            </div>

        </section>

        <div class="section-header">
            <h2>Team Workload</h2>

            <span class="section-note">
                Sorted by active job count
            </span>
        </div>

        @if($workloads->isEmpty())

            <div class="empty-state">
                <h3>No technicians found</h3>

                <p>
                    Add active technicians to your company
                    to start monitoring their workloads.
                </p>

                <a href="{{ route('team.index') }}" class="button">
                    Manage Team
                </a>
            </div>

        @else

            <section class="technician-grid">

                @foreach($workloads as $workload)

                    @php
                        $technician = $workload['technician'];
                        $level = $workload['workload_level'];
                        $active = $workload['active'];
                        $progress = min(100, ($active / 8) * 100);
                        $appointment = $workload['next_appointment'];
                    @endphp

                    <article class="technician-card">

                        <div class="technician-header">

                            <div class="avatar">
                                {{ strtoupper(substr($technician->name, 0, 1)) }}
                            </div>

                            <div>
                                <h3 class="technician-name">
                                    {{ $technician->name }}
                                </h3>

                                <div class="technician-email">
                                    {{ $technician->email }}
                                </div>
                            </div>

                            <span class="workload-badge {{ $level }}">
                                {{ ucfirst($level) }}
                            </span>

                        </div>

                        <div class="workload-summary">
                            <div>
                                <span class="active-number">
                                    {{ $active }}
                                </span>

                                <span class="muted">
                                    active jobs
                                </span>
                            </div>

                            <span class="muted">
                                {{ $workload['total'] }} total
                            </span>
                        </div>

                        <div class="progress-track">
                            <div
                                class="progress-fill {{ $level }}"
                                style="width: {{ $progress }}%"
                            ></div>
                        </div>

                        <div class="job-counts">

                            <div class="job-count">
                                <strong>{{ $workload['open'] }}</strong>
                                <span>Open</span>
                            </div>

                            <div class="job-count">
                                <strong>{{ $workload['assigned'] }}</strong>
                                <span>Assigned</span>
                            </div>

                            <div class="job-count">
                                <strong>{{ $workload['in_progress'] }}</strong>
                                <span>In Progress</span>
                            </div>

                            <div class="job-count">
                                <strong>{{ $workload['completed'] }}</strong>
                                <span>Completed</span>
                            </div>

                        </div>

                        <div class="appointment">

                            <div class="appointment-title">
                                Next Scheduled Appointment
                            </div>

                            @if($appointment)

                                <a href="{{ route('maintenance.show', $appointment) }}">
                                    #{{ $appointment->id }}
                                    {{ $appointment->title }}
                                </a>

                                <div class="appointment-meta">
                                    {{ $appointment->scheduled_start_at->format('M j, Y') }}
                                    at
                                    {{ $appointment->scheduled_start_at->format('g:i A') }}

                                    @if($appointment->unit?->property)
                                        <div>
                                            {{ $appointment->unit->property->name }}
                                            · Unit {{ $appointment->unit->unit_number }}
                                        </div>
                                    @endif
                                </div>

                            @else

                                <div class="muted">
                                    No upcoming appointments scheduled.
                                </div>

                            @endif

                        </div>

                    </article>

                @endforeach

            </section>

        @endif

    </main>

</body>
</html>
