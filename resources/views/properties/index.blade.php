
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Properties | FixFlow</title>

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
            padding: 58px 24px 90px;
        }

        .eyebrow {
            font-size: 13px;
            color: #0071e3;
            font-weight: 700;
            letter-spacing: .07em;
            text-transform: uppercase;
        }

        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            margin-bottom: 30px;
        }

        h1 {
            font-size: clamp(36px, 5vw, 58px);
            letter-spacing: -.045em;
            margin: 12px 0;
        }

        .sub {
            color: #6e6e73;
            margin: 0;
            font-size: 15px;
            line-height: 1.6;
        }

        .add-button,
        .button {
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
            text-decoration: none;
        }

        .add-button:hover,
        .button:hover {
            background: #0077ed;
            color: #ffffff;
            text-decoration: none;
        }

        .add-button {
            white-space: nowrap;
        }

        .tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 24px;
            padding: 6px;
            width: fit-content;
            max-width: 100%;
            background: #e8e8ed;
            border-radius: 16px;
        }

        .tab {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 12px 18px;
            border-radius: 12px;
            color: #6e6e73;
            font-size: 14px;
            font-weight: 650;
            white-space: nowrap;
        }

        .tab:hover {
            color: #1d1d1f;
            text-decoration: none;
        }

        .tab.active {
            background: #ffffff;
            color: #1d1d1f;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .06);
        }

        .tab-count {
            display: inline-block;
            padding: 3px 8px;
            background: #f5f5f7;
            border-radius: 999px;
            font-size: 12px;
        }

        .search-panel {
            background: #ffffff;
            border: 1px solid #e8e8ed;
            border-radius: 22px;
            padding: 24px;
            margin-bottom: 24px;
        }

        .search-label {
            display: block;
            font-size: 14px;
            font-weight: 650;
            margin-bottom: 12px;
        }

        .search-form {
            display: flex;
            gap: 12px;
        }

        .search-input {
            flex: 1;
            min-width: 0;
            min-height: 50px;
            padding: 13px 16px;
            border: 1px solid #d2d2d7;
            border-radius: 13px;
            background: #ffffff;
            color: #1d1d1f;
            font: inherit;
            font-size: 15px;
        }

        .search-input:focus {
            outline: none;
            border-color: #0071e3;
            box-shadow: 0 0 0 4px rgba(0, 113, 227, .12);
        }

        .search-button {
            min-height: 50px;
            padding: 12px 25px;
            border: 0;
            border-radius: 13px;
            background: #0071e3;
            color: #ffffff;
            font: inherit;
            font-size: 14px;
            font-weight: 650;
            cursor: pointer;
        }

        .search-button:hover {
            background: #0077ed;
        }

        .clear-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 50px;
            padding: 12px 20px;
            border-radius: 13px;
            background: #f5f5f7;
            color: #1d1d1f;
            font-size: 14px;
            font-weight: 650;
        }

        .clear-button:hover {
            background: #e8e8ed;
            text-decoration: none;
        }

        .search-hint {
            color: #86868b;
            font-size: 12px;
            margin-top: 12px;
        }

        .summary {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 34px;
        }

        .stat,
        .property {
            background: #ffffff;
            border: 1px solid #e8e8ed;
            border-radius: 22px;
            padding: 26px;
        }

        .stat small,
        .metric small {
            display: block;
            color: #6e6e73;
            font-size: 13px;
        }

        .stat strong {
            display: block;
            margin-top: 12px;
            font-size: 34px;
        }

        .results-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 20px;
        }

        .results-heading h2 {
            margin: 0;
            font-size: 22px;
            letter-spacing: -.025em;
        }

        .results-count {
            color: #6e6e73;
            font-size: 13px;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .property {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .property-heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }

        .property h2 {
            margin: 0;
            font-size: 24px;
            letter-spacing: -.025em;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 650;
            white-space: nowrap;
        }

        .status-active {
            background: #e8f7ed;
            color: #247a43;
        }

        .status-archived {
            background: #f0f0f2;
            color: #6e6e73;
        }

        .muted {
            color: #6e6e73;
            font-size: 14px;
            line-height: 1.5;
        }

        .archive-date {
            margin-top: 5px;
            font-size: 12px;
            color: #86868b;
        }

        .metrics {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin: 20px 0;
        }

        .metric {
            background: #f5f5f7;
            padding: 15px;
            border-radius: 14px;
            min-width: 0;
        }

        .metric strong {
            display: block;
            margin-top: 7px;
            font-size: 23px;
        }

        .property-actions {
            margin-top: auto;
            padding-top: 4px;
        }

        .empty {
            background: #ffffff;
            border: 1px solid #e8e8ed;
            border-radius: 22px;
            padding: 35px;
            color: #6e6e73;
            line-height: 1.6;
        }

        .empty h2 {
            color: #1d1d1f;
            margin: 0 0 10px;
            font-size: 22px;
        }

        .empty p {
            margin: 0 0 24px;
        }

        .alert {
            margin-bottom: 24px;
            padding: 16px 20px;
            border: 1px solid #ccebd5;
            background: #e8f7ed;
            color: #247a43;
            border-radius: 14px;
            font-size: 14px;
        }

        @media (max-width: 700px) {
            .container {
                padding: 36px 20px 70px;
            }

            .page-header {
                flex-direction: column;
                align-items: stretch;
            }

            .add-button {
                width: 100%;
            }

            .tabs {
                width: 100%;
            }

            .tab {
                flex: 1;
                padding: 12px 10px;
            }

            .search-form {
                flex-direction: column;
            }

            .search-button,
            .clear-button {
                width: 100%;
            }

            .summary,
            .cards {
                grid-template-columns: 1fr;
            }

            .metrics {
                gap: 7px;
            }

            .metric {
                padding: 11px;
            }

            .results-heading {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>

<body>

    <x-fixflow-nav />

    @php
        $demoMemberships = \App\Models\CompanyMembership::query()
            ->where('user_id', auth()->id())
            ->where('status', 'active')
            ->pluck('role');

        $isDemoViewerOnly = $demoMemberships->isNotEmpty()
            && $demoMemberships->every(
                fn ($role) => $role === 'demo_viewer'
            );
    @endphp

    <main class="container">

        @if ($isDemoViewerOnly)
            <div class="alert" role="status">
                <strong>Read-only demo.</strong>
                You can explore properties and maintenance records,
                but editing is disabled.
            </div>
        @endif

        <div class="eyebrow">
            Property Management
        </div>

        <div class="page-header">
            <div>
                <h1>Your properties.</h1>

                <p class="sub">
                    Manage buildings, units, maintenance activity,
                    and archived properties across your companies.
                </p>
            </div>

            @unless ($isDemoViewerOnly)
                <a
                    href="{{ route('properties.create') }}"
                    class="add-button"
                >
                    <span aria-hidden="true">+</span>
                    Add Property
                </a>
            @endunless
        </div>

        @if (session('success'))
            <div class="alert" role="status">
                {{ session('success') }}
            </div>
        @endif

        <!-- Active / Archived Tabs -->
        <nav class="tabs" aria-label="Property status">

            <a
                href="{{ route('properties.index', ['status' => 'active']) }}"
                class="tab {{ $status === 'active' ? 'active' : '' }}"
                @if ($status === 'active') aria-current="page" @endif
            >
                Active
                <span class="tab-count">{{ $activeCount }}</span>
            </a>

            <a
                href="{{ route('properties.index', ['status' => 'archived']) }}"
                class="tab {{ $status === 'archived' ? 'active' : '' }}"
                @if ($status === 'archived') aria-current="page" @endif
            >
                Archived
                <span class="tab-count">{{ $archivedCount }}</span>
            </a>

        </nav>

        <!-- Property Search -->
        <section class="search-panel">

            <label for="property-search" class="search-label">
                Search {{ $status }} properties
            </label>

            <form
                action="{{ route('properties.index') }}"
                method="GET"
                class="search-form"
                role="search"
            >
                <input
                    type="hidden"
                    name="status"
                    value="{{ $status }}"
                >

                <input
                    type="search"
                    name="search"
                    id="property-search"
                    class="search-input"
                    value="{{ $search }}"
                    placeholder="Search by name, address, city, state, or ZIP..."
                    maxlength="255"
                    autocomplete="off"
                >

                <button type="submit" class="search-button">
                    Search
                </button>

                @if (filled($search))
                    <a
                        href="{{ route('properties.index', ['status' => $status]) }}"
                        class="clear-button"
                    >
                        Clear Search
                    </a>
                @endif
            </form>

            <div class="search-hint">
                Search within your {{ $status }} properties.
            </div>

        </section>

        <!-- Summary Statistics -->
        <div class="summary">

            <div class="stat">
                <small>
                    {{ filled($search) ? 'Matching properties' : ucfirst($status) . ' properties' }}
                </small>
                <strong>{{ $properties->count() }}</strong>
            </div>

            <div class="stat">
                <small>Total units</small>
                <strong>{{ $properties->sum('units_count') }}</strong>
            </div>

            <div class="stat">
                <small>Active requests</small>
                <strong>{{ $properties->sum('active_requests_count') }}</strong>
            </div>

        </div>

        <!-- Results -->
        <div class="results-heading">

            <h2>
                {{ filled($search) ? 'Search results' : ucfirst($status) . ' properties' }}
            </h2>

            <span class="results-count">
                {{ $properties->count() }}
                {{ \Illuminate\Support\Str::plural('property', $properties->count()) }}
                {{ filled($search) ? 'found' : 'available' }}
            </span>

        </div>

        @if ($properties->isEmpty())

            <div class="empty">

                @if (filled($search))

                    <h2>No matching properties.</h2>

                    <p>
                        We couldn't find any {{ $status }} properties
                        matching "{{ $search }}".
                        Try another search.
                    </p>

                    <a
                        href="{{ route('properties.index', ['status' => $status]) }}"
                        class="button"
                    >
                        Clear Search →
                    </a>

                @elseif ($status === 'archived')

                    <h2>No archived properties.</h2>

                    <p>
                        Properties you archive will appear here.
                        Their units and maintenance history will be preserved.
                    </p>

                    <a
                        href="{{ route('properties.index', ['status' => 'active']) }}"
                        class="button"
                    >
                        View Active Properties →
                    </a>

                @else

                    <h2>No active properties yet.</h2>

                    <p>
                        Add your first property to begin managing
                        buildings, units, and maintenance requests.
                    </p>

                    @unless ($isDemoViewerOnly)
                        <a
                            href="{{ route('properties.create') }}"
                            class="button"
                        >
                            + Create Your First Property
                        </a>
                    @endunless

                @endif

            </div>

        @else

            <div class="cards">

                @foreach ($properties as $property)

                    <article class="property">

                        <div class="property-heading">
                            <h2>{{ $property->name }}</h2>

                            @if ($property->isArchived())
                                <span class="status-badge status-archived">
                                    Archived
                                </span>
                            @else
                                <span class="status-badge status-active">
                                    Active
                                </span>
                            @endif
                        </div>

                        <div class="muted">
                            {{ $property->company?->name ?? 'Company' }}
                        </div>

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

                        @if ($property->isArchived())
                            <div class="archive-date">
                                Archived on
                                {{ $property->archived_at->format('M j, Y') }}
                            </div>
                        @endif

                        <div class="metrics">

                            <div class="metric">
                                <small>Units</small>
                                <strong>{{ $property->units_count }}</strong>
                            </div>

                            <div class="metric">
                                <small>Active</small>
                                <strong>{{ $property->active_requests_count }}</strong>
                            </div>

                            <div class="metric">
                                <small>Completed</small>
                                <strong>{{ $property->completed_requests_count }}</strong>
                            </div>

                        </div>

                        <div class="property-actions">
                            <a
                                href="{{ route('properties.show', $property) }}"
                                class="button"
                            >
                                View Property →
                            </a>
                        </div>

                    </article>

                @endforeach

            </div>

        @endif

    </main>

</body>
</html>
