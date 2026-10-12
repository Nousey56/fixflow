
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
            margin-bottom: 32px;
        }

        .page-heading {
            min-width: 0;
        }

        h1 {
            font-size: clamp(36px, 5vw, 58px);
            letter-spacing: -.045em;
            margin: 12px 0;
        }

        p.sub {
            color: #6e6e73;
            margin: 0;
            font-size: 15px;
            line-height: 1.6;
        }

        .add-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: #0071e3;
            color: #ffffff;
            border-radius: 999px;
            padding: 14px 22px;
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
            align-items: stretch;
            gap: 12px;
        }

        .search-input-wrap {
            position: relative;
            flex: 1;
            min-width: 0;
        }

        .search-icon {
            position: absolute;
            left: 17px;
            top: 50%;
            transform: translateY(-50%);
            color: #86868b;
            font-size: 20px;
            pointer-events: none;
        }

        .search-input {
            display: block;
            width: 100%;
            min-height: 50px;
            padding: 13px 16px 13px 48px;
            border: 1px solid #d2d2d7;
            border-radius: 13px;
            background: #ffffff;
            color: #1d1d1f;
            font: inherit;
            font-size: 15px;
            transition: border-color .2s, box-shadow .2s;
        }

        .search-input:focus {
            outline: none;
            border-color: #0071e3;
            box-shadow: 0 0 0 4px rgba(0, 113, 227, .12);
        }

        .search-input::placeholder {
            color: #86868b;
        }

        .search-button {
            min-height: 50px;
            padding: 12px 25px;
            border: none;
            border-radius: 13px;
            background: #0071e3;
            color: #ffffff;
            font: inherit;
            font-size: 14px;
            font-weight: 650;
            cursor: pointer;
            transition: background .2s;
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
            white-space: nowrap;
        }

        .clear-button:hover {
            background: #e8e8ed;
            text-decoration: none;
        }

        .search-hint {
            color: #86868b;
            font-size: 12px;
            margin-top: 12px;
            line-height: 1.5;
        }

        .results-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 20px;
        }

        .results-heading h2 {
            font-size: 22px;
            letter-spacing: -.025em;
            margin: 0;
        }

        .results-count {
            color: #6e6e73;
            font-size: 13px;
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
            color: #6e6e73;
            display: block;
            font-size: 13px;
        }

        .stat strong {
            font-size: 34px;
            display: block;
            margin-top: 12px;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .property {
            display: flex;
            flex-direction: column;
        }

        .property h2 {
            font-size: 24px;
            letter-spacing: -.025em;
            margin: 0 0 7px;
        }

        .muted {
            color: #6e6e73;
            font-size: 14px;
            line-height: 1.5;
        }

        .metrics {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin: 24px 0;
        }

        .metric {
            background: #f5f5f7;
            padding: 15px;
            border-radius: 14px;
            min-width: 0;
        }

        .metric strong {
            font-size: 23px;
            display: block;
            margin-top: 7px;
        }

        .button {
            display: inline-block;
            align-self: flex-start;
            margin-top: auto;
            background: #0071e3;
            color: #ffffff;
            border-radius: 999px;
            padding: 12px 19px;
            font-size: 14px;
            font-weight: 650;
        }

        .button:hover {
            background: #0077ed;
            text-decoration: none;
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

        .empty .button {
            margin-top: 0;
        }

        @media (max-width: 700px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 22px;
            }

            .cards,
            .summary {
                grid-template-columns: 1fr;
            }

            .search-form {
                flex-direction: column;
            }

            .search-button,
            .clear-button {
                width: 100%;
            }

            .results-heading {
                align-items: flex-start;
                flex-direction: column;
                gap: 6px;
            }

            .metrics {
                gap: 7px;
            }

            .metric {
                padding: 11px;
            }

            .container {
                padding: 36px 20px 70px;
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

        <div class="eyebrow">
            Property Management
        </div>

        <div class="page-header">

            <div class="page-heading">

                <h1>Your properties.</h1>

                <p class="sub">
                    View buildings, units, and maintenance activity
                    across your companies.
                </p>

            </div>

            <a
                href="{{ route('properties.create') }}"
                class="add-button"
            >
                <span class="add-icon" aria-hidden="true">+</span>
                Add Property
            </a>

        </div>

        <!-- Property Search -->
        <section class="search-panel">

            <label for="property-search" class="search-label">
                Search properties
            </label>

            <form
                action="{{ route('properties.index') }}"
                method="GET"
                class="search-form"
                role="search"
            >

                <div class="search-input-wrap">

                    <span class="search-icon" aria-hidden="true">
                        &#9906;
                    </span>

                    <input
                        type="search"
                        name="search"
                        id="property-search"
                        class="search-input"
                        value="{{ $search ?? request('search', '') }}"
                        placeholder="Search by name, address, city, state, or ZIP..."
                        maxlength="255"
                        autocomplete="off"
                    >

                </div>

                <button
                    type="submit"
                    class="search-button"
                >
                    Search
                </button>

                @if (filled($search ?? request('search')))
                    <a
                        href="{{ route('properties.index') }}"
                        class="clear-button"
                    >
                        Clear Search
                    </a>
                @endif

            </form>

            <div class="search-hint">
                Find properties by building name, street address,
                city, state, or ZIP code.
            </div>

        </section>

        <!-- Dashboard Statistics -->
        <div class="summary">

            <div class="stat">
                <small>
                    {{ filled($search ?? '') ? 'Matching properties' : 'Properties' }}
                </small>
                <strong>{{ $properties->count() }}</strong>
            </div>

            <div class="stat">
                <small>
                    {{ filled($search ?? '') ? 'Matching units' : 'Total units' }}
                </small>
                <strong>{{ $properties->sum('units_count') }}</strong>
            </div>

            <div class="stat">
                <small>
                    {{ filled($search ?? '') ? 'Matching active requests' : 'Active requests' }}
                </small>
                <strong>{{ $properties->sum('active_requests_count') }}</strong>
            </div>

        </div>

        <div class="results-heading">

            <h2>
                {{ filled($search ?? '') ? 'Search results' : 'All properties' }}
            </h2>

            <span class="results-count">
                {{ $properties->count() }}
                {{ \Illuminate\Support\Str::plural('property', $properties->count()) }}
                {{ filled($search ?? '') ? 'found' : 'available' }}
            </span>

        </div>

        <!-- Property Cards -->
        @if ($properties->isEmpty())

            <div class="empty">

                @if (filled($search ?? ''))

                    <h2>No matching properties.</h2>

                    <p>
                        We couldn't find any properties matching
                        "{{ $search }}".
                        Try a different name, address, city,
                        state, or ZIP code.
                    </p>

                    <a
                        href="{{ route('properties.index') }}"
                        class="button"
                    >
                        Clear Search →
                    </a>

                @else

                    <h2>No properties yet.</h2>

                    <p>
                        You haven't added any properties for your
                        active management memberships.
                        Create your first property to start
                        organizing buildings and units.
                    </p>

                    <a
                        href="{{ route('properties.create') }}"
                        class="button"
                    >
                        + Create Your First Property
                    </a>

                @endif

            </div>

        @else

            <div class="cards">

                @foreach ($properties as $property)

                    <article class="property">

                        <h2>
                            {{ $property->name }}
                        </h2>

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

                        <div class="metrics">

                            <div class="metric">
                                <small>Units</small>
                                <strong>
                                    {{ $property->units_count }}
                                </strong>
                            </div>

                            <div class="metric">
                                <small>Active</small>
                                <strong>
                                    {{ $property->active_requests_count }}
                                </strong>
                            </div>

                            <div class="metric">
                                <small>Completed</small>
                                <strong>
                                    {{ $property->completed_requests_count }}
                                </strong>
                            </div>

                        </div>

                        <a
                            class="button"
                            href="{{ route('properties.show', $property) }}"
                        >
                            View Property →
                        </a>

                    </article>

                @endforeach

            </div>

        @endif

    </main>

</body>
</html>
