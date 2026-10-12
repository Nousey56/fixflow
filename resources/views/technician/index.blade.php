<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Technician Dashboard | FixFlow</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; background: #f5f5f7; color: #1d1d1f; }
        a { text-decoration: none; }
        main { max-width: 1100px; margin: 65px auto; padding: 0 25px 80px; }
        .eyebrow { color: #0071e3; font-size: 12px; font-weight: 800; letter-spacing: 1px; text-transform: uppercase; }
        h1 { font-size: clamp(38px, 6vw, 58px); letter-spacing: -2px; margin: 12px 0; }
        .subtitle { color: #777; font-size: 17px; line-height: 1.6; }
        .stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 18px; margin: 35px 0; }
        .stat { background: white; padding: 28px; border-radius: 20px; border: 1px solid #ececf0; }
        .stat-label { color: #777; font-size: 13px; font-weight: 600; }
        .stat-number { font-size: 38px; font-weight: 800; margin-top: 12px; }
        .section-heading { margin: 48px 0 22px; font-size: 27px; letter-spacing: -0.7px; }
        .request-card { background: white; border: 1px solid #ececf0; border-radius: 22px; padding: 30px; margin-bottom: 18px; }
        .card-top { display: flex; justify-content: space-between; align-items: center; gap: 15px; }
        .priority { display: inline-block; border-radius: 30px; padding: 8px 12px; font-size: 11px; font-weight: 800; text-transform: uppercase; }
        .priority-low { background: #e8f5e9; color: #237a38; }
        .priority-medium { background: #fff3d6; color: #9b6700; }
        .priority-high { background: #ffe7d6; color: #b54a00; }
        .priority-emergency { background: #ffe5e5; color: #c62828; }
        .status { color: #777; font-size: 13px; font-weight: 600; }
        .request-title { font-size: 23px; margin: 22px 0 8px; }
        .location { color: #777; font-size: 14px; }
        .description { margin: 22px 0; color: #444; line-height: 1.7; white-space: pre-line; }
        .card-footer { border-top: 1px solid #eee; padding-top: 22px; display: flex; justify-content: space-between; align-items: center; gap: 15px; flex-wrap: wrap; }
        .date { color: #888; font-size: 12px; }
        .actions { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
        .action-form { margin: 0; }
        .action-button { border: 0; border-radius: 999px; padding: 11px 18px; font: inherit; font-size: 13px; font-weight: 700; cursor: pointer; background: #0071e3; color: white; }
        .action-button:hover { background: #0065ca; }
        .action-button.complete { background: #237a38; }
        .action-button.complete:hover { background: #19612b; }
        .completion-form { width: 100%; margin-top: 16px; padding: 20px; border-radius: 16px; background: #f5f5f7; }
        .completion-form label { display: block; font-size: 14px; font-weight: 700; margin-bottom: 10px; }
        .completion-form textarea { display: block; width: 100%; min-height: 100px; padding: 13px; border: 1px solid #d2d2d7; border-radius: 12px; font: inherit; resize: vertical; }
        .completion-form .action-button { margin-top: 12px; }
        .completion-hint { font-size: 12px; color: #777; margin-top: 7px; }
        .report { margin-top: 20px; padding: 18px; border-radius: 14px; background: #f5f5f7; font-size: 14px; line-height: 1.6; }
        .report p { white-space: pre-line; }
        .view-link { color: #0071e3; font-size: 14px; font-weight: 700; }
        .done-label { color: #237a38; font-size: 13px; font-weight: 700; }
        .empty { background: white; padding: 65px 25px; border-radius: 22px; text-align: center; color: #777; }
        .empty h3 { color: #1d1d1f; font-size: 22px; }
        .flash { padding: 15px 18px; margin: 25px 0; border-radius: 14px; font-size: 14px; }
        .flash.success { background: #e8f7ed; color: #1b6b35; }
        .flash.error { background: #fff0f0; color: #b42318; }
        .flash ul { margin: 0; padding-left: 20px; }
        @media (max-width: 650px) {
            main { margin-top: 40px; }
            .stats { grid-template-columns: 1fr; }
            .request-card { padding: 22px; }
            .card-footer { align-items: flex-start; flex-direction: column; }
        }
    </style>
</head>
<body>
<x-fixflow-nav />
<main>
    <div class="eyebrow">Technician Workspace</div>
    <h1>My assigned jobs.</h1>
    <p class="subtitle">Welcome back, {{ auth()->user()->name }}. Track your assigned maintenance requests and keep repairs moving.</p>

    @if (session('success'))
        <div class="flash success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="flash error"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    @php
        $totalJobs = $requests->count();
        $activeJobs = $requests->whereIn('status', ['open', 'assigned', 'in_progress'])->count();
        $completedJobs = $requests->where('status', 'completed')->count();
    @endphp

    <section class="stats">
        <div class="stat"><div class="stat-label">Total Assigned</div><div class="stat-number">{{ $totalJobs }}</div></div>
        <div class="stat"><div class="stat-label">Active Jobs</div><div class="stat-number">{{ $activeJobs }}</div></div>
        <div class="stat"><div class="stat-label">Completed</div><div class="stat-number">{{ $completedJobs }}</div></div>
    </section>

    <h2 class="section-heading">Assigned requests.</h2>

    @forelse ($requests as $maintenanceRequest)
        <article class="request-card">
            <div class="card-top">
                <span class="priority priority-{{ strtolower($maintenanceRequest->priority) }}">{{ ucfirst($maintenanceRequest->priority) }}</span>
                <span class="status">{{ ucwords(str_replace('_', ' ', $maintenanceRequest->status)) }}</span>
            </div>
            <h3 class="request-title">{{ $maintenanceRequest->title }}</h3>
            <div class="location">
                {{ $maintenanceRequest->unit?->property?->name ?? 'Unknown Property' }}
                · Unit {{ $maintenanceRequest->unit?->unit_number ?? 'N/A' }}
                · Floor {{ $maintenanceRequest->unit?->floor ?? 'N/A' }}
            </div>
            <p class="description">{{ $maintenanceRequest->description }}</p>
            @if ($maintenanceRequest->status === 'completed')
                <div class="report">
                    <strong>Completion Report</strong>
                    <p>{{ $maintenanceRequest->completion_notes ?: 'No completion report was recorded for this older job.' }}</p>
                    @if ($maintenanceRequest->started_at && $maintenanceRequest->completed_at)
                        <div>Started: {{ \Illuminate\Support\Carbon::parse($maintenanceRequest->started_at)->format('M j, Y g:i A') }}</div>
                        <div>Completed: {{ \Illuminate\Support\Carbon::parse($maintenanceRequest->completed_at)->format('M j, Y g:i A') }}</div>
                        <div>Duration: {{ \Illuminate\Support\Carbon::parse($maintenanceRequest->started_at)->diffForHumans(\Illuminate\Support\Carbon::parse($maintenanceRequest->completed_at), true) }}</div>
                    @else
                        <div>Repair duration unavailable for this job.</div>
                    @endif
                </div>
            @endif
            <div class="card-footer">
                <span class="date">Created {{ $maintenanceRequest->created_at->format('M j, Y') }}</span>
                <div class="actions">
                    @if (in_array($maintenanceRequest->status, ['open', 'assigned'], true))
                        <form class="action-form" action="{{ route('technician.jobs.start', $maintenanceRequest) }}" method="POST">
                            @csrf
                            <button class="action-button" type="submit">Start Job</button>
                        </form>
                    @elseif ($maintenanceRequest->status === 'completed')
                        <span class="done-label">✓ Job completed</span>
                    @endif
                    <a href="{{ route('maintenance.show', $maintenanceRequest) }}" class="view-link">View Request &rarr;</a>
                </div>
            </div>
            @if ($maintenanceRequest->status === 'in_progress')
                <form class="completion-form" action="{{ route('technician.jobs.complete', $maintenanceRequest) }}" method="POST">
                    @csrf
                    <label for="completion_notes_{{ $maintenanceRequest->id }}">Completion notes (required)</label>
                    <textarea id="completion_notes_{{ $maintenanceRequest->id }}" name="completion_notes" minlength="10" maxlength="5000" required placeholder="Describe the repair performed, parts replaced, and checks completed...">{{ old('completion_notes') }}</textarea>
                    <div class="completion-hint">At least 10 characters. Notes are saved to the job history.</div>
                    <button class="action-button complete" type="submit">Complete Job</button>
                </form>
            @endif
        </article>
    @empty
        <div class="empty">
            <h3>No assigned jobs yet.</h3>
            <p>Maintenance requests assigned to you will appear here.</p>
        </div>
    @endforelse
</main>
</body>
</html>
