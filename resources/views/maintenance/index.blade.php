<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FixFlow | Work Orders</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #f5f5f7; color: #1d1d1f; font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "Helvetica Neue", Arial, sans-serif; -webkit-font-smoothing: antialiased; }
        a { text-decoration: none; }
        .container { max-width: 1160px; margin: 0 auto; padding: 58px 24px 100px; }
        .eyebrow { color: #0071e3; font-size: 12px; font-weight: 700; letter-spacing: 1.6px; text-transform: uppercase; }
        .header { display: flex; justify-content: space-between; align-items: center; gap: 20px; margin: 12px 0 32px; }
        h1 { font-size: clamp(36px, 5vw, 54px); letter-spacing: -2.5px; line-height: 1.05; margin: 0 0 14px; }
        .subtitle { color: #6e6e73; font-size: 16px; margin: 0; }
        .primary-button { display: inline-flex; align-items: center; justify-content: center; padding: 13px 21px; border-radius: 999px; background: #0071e3; color: white; font-size: 14px; font-weight: 600; white-space: nowrap; }
        .primary-button:hover { background: #0077ed; }
        .stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; margin-bottom: 28px; }
        .stat { background: white; border: 1px solid #e5e5ea; border-radius: 22px; padding: 24px; }
        .stat-label { color: #6e6e73; font-size: 13px; margin-bottom: 13px; }
        .stat-number { font-size: 36px; font-weight: 700; letter-spacing: -1.5px; }
        .panel { background: white; border: 1px solid #e5e5ea; border-radius: 24px; padding: 24px; margin-bottom: 26px; }
        .panel-title { font-size: 18px; font-weight: 700; letter-spacing: -.5px; margin: 0 0 18px; }
        .filters { display: grid; grid-template-columns: minmax(200px, 2fr) repeat(2, minmax(140px, 1fr)) auto; gap: 12px; align-items: end; }
        .field label { display: block; color: #6e6e73; font-size: 12px; font-weight: 600; margin-bottom: 8px; }
        .field input, .field select { width: 100%; height: 44px; border: 1px solid #d2d2d7; border-radius: 12px; padding: 0 13px; background: #fff; color: #1d1d1f; font: inherit; font-size: 14px; }
        .field input:focus, .field select:focus { outline: 2px solid #0071e3; outline-offset: 1px; }
        .clear-button { border: 1px solid #d2d2d7; border-radius: 12px; height: 44px; background: white; color: #0071e3; padding: 0 16px; font-weight: 600; cursor: pointer; }
        .list-heading { display: flex; justify-content: space-between; align-items: baseline; gap: 12px; margin: 32px 0 16px; }
        .list-heading h2 { margin: 0; font-size: 24px; letter-spacing: -.8px; }
        .muted { color: #6e6e73; font-size: 13px; }
        .orders { display: grid; gap: 14px; }
        .order { background: white; border: 1px solid #e5e5ea; border-radius: 22px; padding: 24px; }
        .order-top { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 14px; }
        .tags { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
        .tag { display: inline-flex; align-items: center; border-radius: 999px; padding: 6px 10px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .35px; }
        .priority-low { background: #e9f7ef; color: #217a3b; }
        .priority-medium { background: #fff4d6; color: #8a6100; }
        .priority-high { background: #ffebe8; color: #b42318; }
        .priority-emergency { background: #ff3b30; color: white; }
        .status-open { background: #f1f1f4; color: #55555a; }
        .status-assigned { background: #e9f2ff; color: #005bb5; }
        .status-in_progress { background: #fff3df; color: #916000; }
        .status-completed { background: #e8f7ed; color: #237b41; }
        .status-cancelled { background: #f1f1f4; color: #55555a; }
        .order h3 { margin: 0 0 9px; font-size: 22px; letter-spacing: -.6px; }
        .location { color: #6e6e73; font-size: 14px; margin: 0; }
        .description { color: #424245; font-size: 14px; line-height: 1.55; margin: 17px 0; overflow-wrap: anywhere; }
        .order-bottom { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; border-top: 1px solid #ededf0; padding-top: 16px; }
        .assignee { color: #6e6e73; font-size: 13px; }
        .assignee strong { color: #1d1d1f; }
        .view-link { color: #0071e3; font-weight: 600; font-size: 14px; }
        .view-link:hover { text-decoration: underline; }
        .empty { text-align: center; padding: 55px 20px; background: white; border: 1px solid #e5e5ea; border-radius: 22px; }
        .empty h3 { margin: 0 0 8px; font-size: 21px; }
        .empty p { color: #6e6e73; margin: 0; }
        .success { background: #e8f7ed; border: 1px solid #c6e8d0; color: #237b41; border-radius: 14px; padding: 15px 18px; margin-bottom: 24px; }
        [hidden] { display: none !important; }
        @media (max-width: 850px) { .stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } .filters { grid-template-columns: repeat(2, minmax(0, 1fr)); } .field.search-field { grid-column: 1 / -1; } }
        @media (max-width: 550px) { .container { padding: 38px 16px 65px; } .header { flex-direction: column; align-items: flex-start; } .stats { gap: 10px; } .stat { padding: 18px; } .stat-number { font-size: 30px; } .filters { grid-template-columns: 1fr; } .field.search-field { grid-column: auto; } .clear-button { width: 100%; } .panel, .order { padding: 18px; } }
    </style>
</head>
<body>
    <x-fixflow-nav />

    @php
        $canCreate = \App\Models\CompanyMembership::query()
            ->where('user_id', auth()->id())
            ->where('status', 'active')
            ->whereIn('role', ['company_admin', 'manager'])
            ->exists();

        $totalOrders = $requests->count();
        $openOrders = $requests->where('status', 'open')->count();
        $inProgressOrders = $requests->where('status', 'in_progress')->count();
        $completedOrders = $requests->where('status', 'completed')->count();
    @endphp

    <main class="container">
        <div class="eyebrow">FixFlow Enterprise · Maintenance</div>
        <div class="header">
            <div>
                <h1>Work orders.</h1>
                <p class="subtitle">Manage repairs, assignments, and progress across your properties.</p>
            </div>
            @if ($canCreate)
                <a class="primary-button" href="{{ route('maintenance.create') }}">+ New Work Order</a>
            @endif
        </div>

        @if (session('success'))
            <div class="success" role="status">{{ session('success') }}</div>
        @endif

        <section class="stats" aria-label="Work order statistics">
            <div class="stat"><div class="stat-label">Total work orders</div><div class="stat-number">{{ $totalOrders }}</div></div>
            <div class="stat"><div class="stat-label">Open</div><div class="stat-number">{{ $openOrders }}</div></div>
            <div class="stat"><div class="stat-label">In progress</div><div class="stat-number">{{ $inProgressOrders }}</div></div>
            <div class="stat"><div class="stat-label">Completed</div><div class="stat-number">{{ $completedOrders }}</div></div>
        </section>

        <section class="panel" aria-label="Filter work orders">
            <h2 class="panel-title">Find a work order</h2>
            <div class="filters">
                <div class="field search-field">
                    <label for="order-search">Search</label>
                    <input id="order-search" type="search" placeholder="Issue, property, unit, or technician..." autocomplete="off">
                </div>
                <div class="field">
                    <label for="order-status">Status</label>
                    <select id="order-status">
                        <option value="">All statuses</option>
                        <option value="open">Open</option>
                        <option value="assigned">Assigned</option>
                        <option value="in_progress">In progress</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="field">
                    <label for="order-priority">Priority</label>
                    <select id="order-priority">
                        <option value="">All priorities</option>
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="emergency">Emergency</option>
                    </select>
                </div>
                <button class="clear-button" type="button" id="clear-filters">Clear</button>
            </div>
        </section>

        <div class="list-heading">
            <h2>All work orders</h2>
            <span class="muted" id="visible-count">{{ $totalOrders }} {{ \Illuminate\Support\Str::plural('work order', $totalOrders) }}</span>
        </div>

        <section class="orders" id="orders" aria-label="Work order list">
            @forelse ($requests as $workOrder)
                @php
                    $propertyName = $workOrder->unit?->property?->name ?? 'Unknown property';
                    $unitNumber = $workOrder->unit?->unit_number ?? 'Unknown';
                    $technicianName = $workOrder->assignedTechnician?->name ?? 'Unassigned';
                    $searchable = implode(' ', [
                        $workOrder->title,
                        $workOrder->description,
                        $propertyName,
                        $unitNumber,
                        $technicianName,
                        (string) $workOrder->id,
                    ]);
                @endphp
                <article class="order" data-order data-search="{{ \Illuminate\Support\Str::lower($searchable) }}" data-status="{{ $workOrder->status }}" data-priority="{{ $workOrder->priority }}">
                    <div class="order-top">
                        <div class="tags">
                            <span class="tag priority-{{ $workOrder->priority }}">{{ ucfirst($workOrder->priority) }} priority</span>
                            <span class="tag status-{{ $workOrder->status }}">{{ str_replace('_', ' ', ucfirst($workOrder->status)) }}</span>
                        </div>
                        <span class="muted">WO #{{ $workOrder->id }}</span>
                    </div>
                    <h3>{{ $workOrder->title }}</h3>
                    <p class="location">
                        {{ $propertyName }} · Unit {{ $unitNumber }}
                        @if ($workOrder->unit?->floor !== null)
                            · Floor {{ $workOrder->unit->floor }}
                        @endif
                    </p>
                    <p class="description">{{ \Illuminate\Support\Str::limit($workOrder->description, 240) }}</p>
                    <div class="order-bottom">
                        <span class="assignee">Assigned to: <strong>{{ $technicianName }}</strong></span>
                        <a class="view-link" href="{{ route('maintenance.show', $workOrder) }}">View work order →</a>
                    </div>
                </article>
            @empty
                <div class="empty">
                    <h3>No work orders yet.</h3>
                    <p>Maintenance requests will appear here when they are created.</p>
                </div>
            @endforelse
        </section>

        @if ($totalOrders > 0)
            <div class="empty" id="no-filter-results" hidden>
                <h3>No matching work orders.</h3>
                <p>Try a different search or clear your filters.</p>
            </div>
        @endif
    </main>

    <script>
        (() => {
            const search = document.getElementById('order-search');
            const status = document.getElementById('order-status');
            const priority = document.getElementById('order-priority');
            const clear = document.getElementById('clear-filters');
            const orders = [...document.querySelectorAll('[data-order]')];
            const count = document.getElementById('visible-count');
            const empty = document.getElementById('no-filter-results');

            function filterOrders() {
                const term = search.value.trim().toLocaleLowerCase();
                let visible = 0;

                orders.forEach((order) => {
                    const matches = order.dataset.search.includes(term)
                        && (!status.value || order.dataset.status === status.value)
                        && (!priority.value || order.dataset.priority === priority.value);
                    order.hidden = !matches;
                    if (matches) visible++;
                });

                count.textContent = `${visible} work order${visible === 1 ? '' : 's'}`;
                if (empty) empty.hidden = visible !== 0;
            }

            [search, status, priority].forEach((control) => control.addEventListener('input', filterOrders));
            clear.addEventListener('click', () => {
                search.value = '';
                status.value = '';
                priority.value = '';
                filterOrders();
                search.focus();
            });
        })();
    </script>
</body>
</html>
