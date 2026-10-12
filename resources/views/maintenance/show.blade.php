@php
    $isManager = (bool) ($canEditPriority ?? false);
    $isAdmin = (bool) ($canAssign ?? false);
    $isTechnician = ! $isManager;
    $started = $request->started_at ? \Illuminate\Support\Carbon::parse($request->started_at) : null;
    $completed = $request->completed_at ? \Illuminate\Support\Carbon::parse($request->completed_at) : null;
    $durationSeconds = ($started && $completed && $completed->greaterThanOrEqualTo($started))
        ? $started->diffInSeconds($completed)
        : null;
    $durationLabel = $durationSeconds === null ? null : (
        $durationSeconds < 60 ? $durationSeconds . ' seconds' :
        (floor($durationSeconds / 3600) > 0 ? floor($durationSeconds / 3600) . 'h ' : '') .
        floor(($durationSeconds % 3600) / 60) . 'm ' . ($durationSeconds % 60) . 's'
    );
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $request->title }} | FixFlow</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Text', 'Helvetica Neue', Arial, sans-serif; background: #f5f5f7; color: #1d1d1f; -webkit-font-smoothing: antialiased; }
        a { color: #0066cc; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .button { display: inline-flex; justify-content: center; align-items: center; border: 0; background: #0071e3; color: white; border-radius: 999px; padding: 11px 19px; font-weight: 600; font-size: 14px; cursor: pointer; }
        .button:hover { background: #0077ed; text-decoration: none; }
        .page { max-width: 960px; padding: 56px 24px 100px; margin: 0 auto; }
        .back { display: inline-block; margin-bottom: 32px; font-size: 14px; }
        .eyebrow { color: #0071e3; text-transform: uppercase; letter-spacing: 1px; font-weight: 700; font-size: 12px; }
        h1 { font-size: clamp(38px, 6vw, 60px); letter-spacing: -2.3px; line-height: 1.08; margin: 12px 0; overflow-wrap: anywhere; }
        .subtitle { color: #6e6e73; font-size: 17px; margin-bottom: 32px; }
        .card { background: white; border: 1px solid #e8e8ed; border-radius: 24px; padding: 30px; margin: 18px 0; }
        .top-row { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 26px; }
        .priority { display: inline-block; padding: 7px 12px; border-radius: 999px; font-size: 11px; font-weight: 750; text-transform: uppercase; letter-spacing: .3px; }
        .priority-low { background: #e8f5e9; color: #248a3d; }
        .priority-medium { background: #fff4d6; color: #9a6700; }
        .priority-high { background: #ffe5e5; color: #b42318; }
        .priority-emergency { background: #ff3b30; color: white; }
        .status { color: #6e6e73; font-size: 14px; font-weight: 600; }
        h2 { font-size: 21px; letter-spacing: -.5px; margin: 0 0 18px; }
        .label { font-size: 12px; text-transform: uppercase; letter-spacing: .5px; font-weight: 700; color: #6e6e73; margin-bottom: 8px; }
        .value { font-size: 16px; line-height: 1.6; white-space: pre-wrap; overflow-wrap: anywhere; margin: 0; }
        .grid { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 14px; }
        .tile { background: #f5f5f7; border-radius: 16px; padding: 18px; min-width: 0; }
        .tile-value { font-size: 17px; font-weight: 650; overflow-wrap: anywhere; }
        .section { border-top: 1px solid #e8e8ed; margin-top: 25px; padding-top: 25px; }
        .note { background: #f5f5f7; border-radius: 16px; padding: 20px; line-height: 1.6; white-space: pre-wrap; overflow-wrap: anywhere; }
        .muted { color: #6e6e73; font-size: 14px; line-height: 1.6; }
        .success { background: #e8f7ed; color: #1b6b35; border-radius: 12px; padding: 14px 16px; margin-bottom: 20px; }
        .errors { background: #fff0f0; color: #b42318; border-radius: 12px; padding: 14px 16px; margin-bottom: 20px; }
        .errors ul { margin: 0; padding-left: 20px; }
        .form-grid { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: 16px; }
        .field { margin-bottom: 18px; }
        .field label { display: block; font-size: 14px; font-weight: 650; margin-bottom: 9px; }
        .field select, .field textarea { width: 100%; border: 1px solid #d2d2d7; border-radius: 13px; padding: 14px; font: inherit; background: white; color: #1d1d1f; }
        .field textarea { min-height: 120px; resize: vertical; }
        .field select:focus, .field textarea:focus { outline: 3px solid #cce4ff; border-color: #0071e3; }
        .history-item { padding: 18px 0; border-top: 1px solid #e8e8ed; }
        .history-item:first-of-type { border-top: 0; }
        .history-date { font-size: 13px; color: #6e6e73; margin-bottom: 9px; }
        .history-item p { margin: 8px 0 0; line-height: 1.6; overflow-wrap: anywhere; }
        .history-notes { white-space: pre-wrap; }
        @media (max-width: 700px) { .page { padding: 36px 16px 70px; } .card { padding: 22px; border-radius: 20px; } .grid, .form-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <x-fixflow-nav />

    <main class="page">
        <a class="back" href="{{ $isTechnician ? route('technician.index') : route('maintenance.index') . '#requests' }}">← {{ $isTechnician ? 'Back to My Jobs' : 'Back to requests' }}</a>
        <div class="eyebrow">Maintenance Request #{{ $request->id }}</div>
        <h1>{{ $request->title }}</h1>
        <p class="subtitle">{{ $request->unit->property->name }} · Unit {{ $request->unit->unit_number }}@if ($request->unit->floor) · Floor {{ $request->unit->floor }}@endif</p>

        @if (session('success'))
            <div class="success" role="status">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="errors" role="alert"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <section class="card">
            <div class="top-row">
                <span class="priority priority-{{ strtolower($request->priority) }}">{{ $request->priority }}</span>
                <span class="status">{{ ucwords(str_replace('_', ' ', $request->status)) }}</span>
            </div>
            <h2>Request details</h2>
            <div class="label">Description / Comments</div>
            <p class="value">{{ $request->description }}</p>
            <div class="section">
                <div class="grid">
                    <div class="tile"><div class="label">Property</div><div class="tile-value">{{ $request->unit->property->name }}</div></div>
                    <div class="tile"><div class="label">Unit</div><div class="tile-value">{{ $request->unit->unit_number }}</div></div>
                    <div class="tile"><div class="label">Floor</div><div class="tile-value">{{ $request->unit->floor ?? '—' }}</div></div>
                </div>
            </div>
            <div class="section">
                <div class="grid">
                    <div><div class="label">Assigned technician</div><p class="value">{{ $request->assignedTechnician?->name ?? 'Unassigned' }}</p></div>
                    <div><div class="label">Priority</div><p class="value">{{ ucfirst($request->priority) }}</p></div>
                    <div><div class="label">Status</div><p class="value">{{ ucwords(str_replace('_', ' ', $request->status)) }}</p></div>
                </div>
            </div>
            <div class="section"><div class="label">Request created</div><p class="muted">{{ $request->created_at->format('F j, Y \a\t g:i A') }}</p></div>
        </section>

        @if ($request->status === 'completed')
            <section class="card">
                <h2>Completion report</h2>
                @if (filled($request->completion_notes))
                    <div class="label">Work completed</div>
                    <div class="note">{{ $request->completion_notes }}</div>
                @else
                    <p class="muted">No completion report was recorded for this older job.</p>
                @endif
                <div class="section">
                    <div class="grid">
                        <div><div class="label">Started</div><div class="tile-value">{{ $started?->format('M j, Y g:i A') ?? 'Not recorded' }}</div></div>
                        <div><div class="label">Completed</div><div class="tile-value">{{ $completed?->format('M j, Y g:i A') ?? 'Not recorded' }}</div></div>
                        <div><div class="label">Repair duration</div><div class="tile-value">{{ $durationLabel ?? 'Unavailable' }}</div></div>
                    </div>
                </div>
            </section>
        @elseif ($started)
            <section class="card">
                <h2>Work progress</h2>
                <div class="label">Work started</div>
                <p class="value">{{ $started->format('M j, Y g:i A') }}</p>
            </section>
        @endif

        @if ($isTechnician)
            <section class="card">
                <h2>Job actions</h2>
                <p class="muted">Job status changes and completion notes are managed through your Technician Dashboard. This page is read-only.</p>
                <a href="{{ route('technician.index') }}" class="button">Go to My Jobs →</a>
            </section>
        @else
            <section class="card">
                <h2>Update request</h2>
                <form method="POST" action="{{ route('maintenance.update', $request) }}">
                    @csrf
                    @method('PATCH')
                    <div class="form-grid">
                        <div class="field">
                            <label for="status">Progress / Status</label>
                            <select name="status" id="status" required>
                                @foreach (['open' => 'Open', 'assigned' => 'Assigned', 'in_progress' => 'In Progress'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status', $request->status) === $value)>{{ $label }}</option>
                                @endforeach
                                @if ($request->status === 'completed')
                                    <option value="completed" @selected(old('status', $request->status) === 'completed')>Completed</option>
                                @endif
                            </select>
                            <p class="muted">Technicians complete jobs with required notes from My Jobs.</p>
                        </div>
                        <div class="field">
                            <label for="priority">Priority</label>
                            <select name="priority" id="priority" required>
                                @foreach (['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'emergency' => 'Emergency'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('priority', $request->priority) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @if ($isAdmin)
                        <div class="field">
                            <label for="assigned_to">Assign technician</label>
                            <select name="assigned_to" id="assigned_to">
                                <option value="" @selected(old('assigned_to', $request->assigned_to) === null || old('assigned_to', $request->assigned_to) === '')>Unassigned</option>
                                @foreach ($technicians ?? collect() as $technician)
                                    <option value="{{ $technician->id }}" @selected((string) old('assigned_to', $request->assigned_to) === (string) $technician->id)>{{ $technician->name }} ({{ $technician->email }})</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="field">
                        <label for="work_notes">Work notes (optional)</label>
                        <textarea name="work_notes" id="work_notes" placeholder="Record an update or other notes...">{{ old('work_notes') }}</textarea>
                    </div>
                    <button type="submit" class="button">Save Changes</button>
                </form>
            </section>
        @endif

        <section class="card">
            <h2>Activity history</h2>
            @forelse ($request->activities->sortByDesc('created_at') as $activity)
                <div class="history-item">
                    <div class="history-date">{{ $activity->created_at?->format('M j, Y \a\t g:i A') ?? 'Date unavailable' }}</div>
                    @if ($activity->old_status !== $activity->new_status)
                        <p>Status: {{ ucwords(str_replace('_', ' ', $activity->old_status ?? 'Unknown')) }} → {{ ucwords(str_replace('_', ' ', $activity->new_status ?? 'Unknown')) }}</p>
                    @endif
                    @if ($activity->old_priority !== $activity->new_priority)
                        <p>Priority: {{ ucfirst($activity->old_priority ?? 'Unknown') }} → {{ ucfirst($activity->new_priority ?? 'Unknown') }}</p>
                    @endif
                    @if (filled($activity->work_notes))
                        <p class="history-notes">{{ $activity->work_notes }}</p>
                    @endif
                </div>
            @empty
                <p class="muted">No updates recorded yet.</p>
            @endforelse
        </section>
    </main>
</body>
</html>
