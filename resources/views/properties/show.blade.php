
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $property->name }} | FixFlow</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f5f5f7;
            color: #1d1d1f;
            font-family: -apple-system, BlinkMacSystemFont,
                "Helvetica Neue", Arial, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        a {
            color: #0071e3;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 50px 24px 90px;
        }

        .back {
            font-size: 14px;
        }

        .eyebrow {
            font-size: 13px;
            color: #0071e3;
            font-weight: 700;
            letter-spacing: .07em;
            text-transform: uppercase;
            margin-top: 32px;
        }

        .property-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 24px;
            margin-top: 12px;
        }

        .property-heading {
            min-width: 0;
        }

        h1 {
            font-size: clamp(36px, 5vw, 58px);
            letter-spacing: -.045em;
            margin: 0 0 12px;
            overflow-wrap: anywhere;
        }

        .muted {
            color: #6e6e73;
            font-size: 14px;
            line-height: 1.6;
        }

        .edit-property-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: #ffffff;
            color: #0071e3;
            border: 1px solid #d2d2d7;
            border-radius: 999px;
            padding: 12px 20px;
            font-size: 14px;
            font-weight: 650;
            white-space: nowrap;
            flex-shrink: 0;
            transition: background .2s, border-color .2s;
        }

        .edit-property-button:hover {
            background: #f0f5ff;
            border-color: #b5d4ff;
            text-decoration: none;
        }

        .property-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }

        .property-actions form {
            margin: 0;
        }

        .archive-button,
        .restore-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            padding: 12px 20px;
            font: inherit;
            font-size: 14px;
            font-weight: 650;
            cursor: pointer;
            white-space: nowrap;
        }

        .archive-button {
            background: #ffffff;
            color: #a33b27;
            border: 1px solid #e8c8c1;
        }

        .archive-button:hover {
            background: #fff2ef;
        }

        .restore-button {
            background: #0071e3;
            color: #ffffff;
            border: 1px solid #0071e3;
        }

        .restore-button:hover {
            background: #0077ed;
        }

        .archive-notice {
            margin: 24px 0;
            padding: 18px 22px;
            background: #fff8e8;
            border: 1px solid #f0ddb4;
            border-radius: 16px;
            color: #6f531f;
            font-size: 14px;
            line-height: 1.6;
        }

        .archive-notice strong {
            display: block;
            margin-bottom: 4px;
            font-size: 15px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            margin: 30px 0;
        }

        .stat,
        .unit {
            background: #ffffff;
            border: 1px solid #e8e8ed;
            border-radius: 22px;
            padding: 25px;
        }

        .stat small {
            display: block;
            color: #6e6e73;
        }

        .stat strong {
            display: block;
            font-size: 34px;
            margin-top: 10px;
        }

        h2 {
            font-size: 26px;
            letter-spacing: -.025em;
            margin: 0;
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin: 38px 0 20px;
        }

        .add-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: #0071e3;
            color: #ffffff;
            border-radius: 999px;
            padding: 13px 21px;
            font-size: 14px;
            font-weight: 650;
            white-space: nowrap;
            flex-shrink: 0;
            transition: background .2s, transform .2s;
        }

        .add-button:hover {
            background: #0077ed;
            text-decoration: none;
            transform: translateY(-1px);
        }

        .add-icon {
            font-size: 19px;
            line-height: 1;
            font-weight: 400;
        }

        .units {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .unit-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
        }

        .unit h3 {
            font-size: 21px;
            margin: 0 0 6px;
        }

        .edit-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 9px 14px;
            border-radius: 999px;
            background: #f0f5ff;
            color: #0071e3;
            font-size: 13px;
            font-weight: 650;
            white-space: nowrap;
            transition: background .2s;
        }

        .edit-button:hover {
            background: #e0edff;
            text-decoration: none;
        }

        .request {
            border-top: 1px solid #e8e8ed;
            padding: 16px 0;
        }

        .request:first-child {
            margin-top: 18px;
        }

        .request-head {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: center;
        }

        .request-title {
            font-weight: 650;
        }

        .status {
            color: #6e6e73;
            font-size: 12px;
            white-space: nowrap;
        }

        .priority {
            font-size: 12px;
            color: #6e6e73;
            margin: 7px 0;
        }

        .empty {
            color: #6e6e73;
            font-size: 14px;
            padding-top: 16px;
        }

        .success {
            background: #e8f7ed;
            color: #1b6b35;
            border: 1px solid #c8e9d0;
            border-radius: 14px;
            padding: 16px 20px;
            font-size: 14px;
            margin: 26px 0;
        }

        @media (max-width: 700px) {
            .stats,
            .units {
                grid-template-columns: 1fr;
            }

            .container {
                padding: 36px 20px 70px;
            }

            .property-header {
                flex-direction: column;
                gap: 18px;
            }

            .property-actions,
            .property-actions form,
            .edit-property-button,
            .archive-button,
            .restore-button {
                width: 100%;
            }

            .request-head {
                align-items: flex-start;
                flex-wrap: wrap;
            }

            .section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }

            .add-button {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <x-fixflow-nav />

    <main class="container">

        <a
            class="back"
            href="{{ route('properties.index') }}"
        >
            ← All properties
        </a>

        <div class="eyebrow">
            {{ $property->company?->name ?? 'Property management' }}
        </div>

        <div class="property-header">

            <div class="property-heading">

                <h1>{{ $property->name }}</h1>

                <div class="muted">
                    {{
                        collect([
                            $property->address,
                            $property->city,
                            $property->state,
                            $property->zip_code
                        ])->filter()->implode(', ')
                    }}
                </div>

            </div>

            <div class="property-actions">
                @if ($property->isArchived())
                    <form method="POST" action="{{ route('properties.restore', $property) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="restore-button">
                            Restore Property
                        </button>
                    </form>
                @else
                    <a
                        href="{{ route('properties.edit', $property) }}"
                        class="edit-property-button"
                    >
                        Edit Property →
                    </a>

                    <form
                        method="POST"
                        action="{{ route('properties.archive', $property) }}"
                        onsubmit="return confirm('Archive this property? Its units and maintenance history will be preserved.');"
                    >
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="archive-button">
                            Archive Property
                        </button>
                    </form>
                @endif
            </div>

        </div>

        @if (session('success'))
            <div class="success" role="status">
                {{ session('success') }}
            </div>
        @endif

        @if ($property->isArchived())
            <div class="archive-notice" role="status">
                <strong>This property is archived.</strong>
                Archived on {{ $property->archived_at->format('F j, Y') }}.
                Its units and maintenance history remain available for reference.
                Restore the property before making changes.
            </div>
        @endif

        @php
            $allRequests = $property->units->flatMap(
                fn ($unit) => $unit->maintenanceRequests
            );

            $activeCount = $allRequests
                ->whereIn('status', [
                    'open',
                    'assigned',
                    'in_progress'
                ])
                ->count();

            $completedCount = $allRequests
                ->where('status', 'completed')
                ->count();
        @endphp

        <div class="stats">

            <div class="stat">
                <small>Units</small>
                <strong>{{ $property->units->count() }}</strong>
            </div>

            <div class="stat">
                <small>Active requests</small>
                <strong>{{ $activeCount }}</strong>
            </div>

            <div class="stat">
                <small>Completed requests</small>
                <strong>{{ $completedCount }}</strong>
            </div>

        </div>

        <div class="section-header">

            <h2>Units and maintenance</h2>

            @unless ($property->isArchived())
                <a
                    href="{{ route('units.create', $property) }}"
                    class="add-button"
                >
                    <span class="add-icon" aria-hidden="true">+</span>
                    Add Unit
                </a>
            @endunless

        </div>

        @if ($property->units->isEmpty())

            <div class="unit muted">
                No units have been added to this property.
                @if (! $property->isArchived())
                    Use the Add Unit button above to create one.
                @endif
            </div>

        @else

            <div class="units">

                @foreach (
                    $property->units->sortBy('unit_number')
                    as $unit
                )

                    <article class="unit">

                        <div class="unit-header">

                            <div>

                                <h3>
                                    Unit {{ $unit->unit_number }}
                                </h3>

                                <div class="muted">
                                    Floor {{ $unit->floor ?? 'Not specified' }}
                                    ·
                                    {{ $unit->maintenanceRequests->count() }}
                                    {{
                                        \Illuminate\Support\Str::plural(
                                            'request',
                                            $unit->maintenanceRequests->count()
                                        )
                                    }}
                                </div>

                            </div>

                            @unless ($property->isArchived())
                                <a
                                    href="{{ route('units.edit', [$property, $unit]) }}"
                                    class="edit-button"
                                    aria-label="Edit Unit {{ $unit->unit_number }}"
                                >
                                    Edit Unit →
                                </a>
                            @endunless

                        </div>

                        @forelse (
                            $unit->maintenanceRequests
                                ->sortByDesc('created_at')
                            as $job
                        )

                            <div class="request">

                                <div class="request-head">

                                    <a
                                        class="request-title"
                                        href="{{ route('maintenance.show', $job) }}"
                                    >
                                        {{ $job->title }} →
                                    </a>

                                    <span class="status">
                                        {{
                                            ucwords(
                                                str_replace(
                                                    '_',
                                                    ' ',
                                                    $job->status
                                                )
                                            )
                                        }}
                                    </span>

                                </div>

                                <div class="priority">
                                    {{ ucfirst($job->priority) }} priority
                                    ·
                                    {{
                                        $job->assignedTechnician?->name
                                        ?? 'Unassigned'
                                    }}
                                </div>

                                <div class="muted">
                                    {{
                                        \Illuminate\Support\Str::limit(
                                            $job->description,
                                            110
                                        )
                                    }}
                                </div>

                            </div>

                        @empty

                            <div class="empty">
                                No maintenance requests for this unit.
                            </div>

                        @endforelse

                    </article>

                @endforeach

            </div>

        @endif

    </main>

</body>
</html>
