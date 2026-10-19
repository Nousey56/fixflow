
@php
    $user = auth()->user();

    $memberships = $user
        ? $user->memberships()
            ->where('status', 'active')
            ->get()
        : collect();

    $isAdmin = $memberships->contains(
        fn ($membership) => $membership->role === 'company_admin'
    );

    $isManager = $memberships->contains(
        fn ($membership) => $membership->role === 'manager'
    );

    $isTechnician = $memberships->contains(
        fn ($membership) => $membership->role === 'technician'
    );

    $canManage = $isAdmin || $isManager;

    $roleLabel = match (true) {
        $isAdmin => 'Administrator',
        $isManager => 'Manager',
        $isTechnician => 'Technician',
        default => 'No active role',
    };

    $roleClass = match (true) {
        $isAdmin => 'ff-role-admin',
        $isManager => 'ff-role-manager',
        $isTechnician => 'ff-role-technician',
        default => 'ff-role-default',
    };
@endphp

<style>
    .ff-navbar {
        background: rgba(255, 255, 255, 0.96);
        border-bottom: 1px solid #e5e5ea;
        position: sticky;
        top: 0;
        z-index: 100;
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
    }

    .ff-nav-inner {
        max-width: 1280px;
        margin: 0 auto;
        min-height: 68px;
        padding: 12px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
    }

    .ff-brand {
        font-size: 23px;
        font-weight: 750;
        letter-spacing: -0.8px;
        color: #0071e3;
        text-decoration: none;
        white-space: nowrap;
    }

    .ff-nav-links {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 18px;
    }

    .ff-nav-links a {
        font-size: 14px;
        color: #515154;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.2s;
    }

    .ff-nav-links a:hover,
    .ff-nav-links a.ff-active {
        color: #0071e3;
    }

    .ff-nav-links a.ff-active {
        font-weight: 700;
    }

    .ff-account {
        display: flex;
        align-items: center;
        gap: 14px;
        padding-left: 18px;
        border-left: 1px solid #e5e5ea;
    }

    .ff-role {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 7px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .ff-role-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: currentColor;
    }

    .ff-role-admin {
        background: #e8f2ff;
        color: #0066cc;
    }

    .ff-role-manager {
        background: #f0eaff;
        color: #6941c6;
    }

    .ff-role-technician {
        background: #e8f7ed;
        color: #237a38;
    }

    .ff-role-default {
        background: #f0f0f2;
        color: #6e6e73;
    }

    .ff-logout {
        border: 0;
        background: transparent;
        color: #6e6e73;
        cursor: pointer;
        font: inherit;
        font-size: 13px;
        padding: 0;
    }

    .ff-logout:hover {
        color: #d92d20;
    }

    .ff-mobile-toggle {
        display: none;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border: 1px solid #e5e5ea;
        border-radius: 12px;
        background: #f5f5f7;
        color: #1d1d1f;
        cursor: pointer;
    }

    .ff-mobile-toggle svg {
        width: 22px;
        height: 22px;
        stroke: currentColor;
        stroke-width: 2;
        fill: none;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .ff-mobile-toggle:focus-visible,
    .ff-nav-links a:focus-visible,
    .ff-logout:focus-visible {
        outline: 2px solid #0071e3;
        outline-offset: 3px;
    }

    @media (max-width: 950px) {
        .ff-nav-inner {
            padding: 12px 18px;
            min-height: 64px;
            flex-wrap: wrap;
            gap: 0;
        }

        .ff-mobile-toggle {
            display: inline-flex;
        }

        .ff-nav-links {
            display: none;
            flex-basis: 100%;
            width: 100%;
            flex-direction: column;
            align-items: stretch;
            gap: 0;
            padding: 16px 0 8px;
        }

        .ff-nav-links.ff-mobile-open {
            display: flex;
        }

        .ff-nav-links a {
            display: block;
            width: 100%;
            box-sizing: border-box;
            padding: 13px 14px;
            border-radius: 10px;
            font-size: 15px;
        }

        .ff-nav-links a:hover {
            background: #f5f5f7;
        }

        .ff-nav-links a.ff-active {
            background: #e8f2ff;
            color: #0071e3;
        }

        .ff-account {
            width: 100%;
            box-sizing: border-box;
            margin-top: 12px;
            padding: 16px 10px 4px;
            border-left: 0;
            border-top: 1px solid #e5e5ea;
            justify-content: space-between;
        }
    }

    @media (min-width: 951px) {
        .ff-nav-links {
            display: flex !important;
        }
    }
</style>

<nav class="ff-navbar" aria-label="Main navigation">
    <div class="ff-nav-inner">

        <a class="ff-brand" href="{{ route('maintenance.index') }}">
            FixFlow.
        </a>

        <button
            type="button"
            class="ff-mobile-toggle"
            id="ff-mobile-toggle"
            aria-label="Open navigation menu"
            aria-expanded="false"
            aria-controls="ff-nav-links"
        >
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M4 7h16"></path>
                <path d="M4 12h16"></path>
                <path d="M4 17h16"></path>
            </svg>
        </button>

        <div class="ff-nav-links" id="ff-nav-links">

            <a
                href="{{ route('maintenance.index') }}"
                class="{{ request()->routeIs('maintenance.index') ? 'ff-active' : '' }}"
            >
                Overview
            </a>

            <a href="{{ route('maintenance.index') }}#requests">
                Requests
            </a>

            @if ($canManage)
                <a
                    href="{{ route('dispatch.index') }}"
                    class="{{ request()->routeIs('dispatch.*') ? 'ff-active' : '' }}"
                    @if (request()->routeIs('dispatch.*')) aria-current="page" @endif
                >
                    Dispatch
                </a>

                <a
                    href="{{ route('calendar.index') }}"
                    class="{{ request()->routeIs('calendar.*') ? 'ff-active' : '' }}"
                    @if (request()->routeIs('calendar.*')) aria-current="page" @endif
                >
                    Calendar
                </a>

                <a
                    href="{{ route('workload.index') }}"
                    class="{{ request()->routeIs('workload.*') ? 'ff-active' : '' }}"
                    @if (request()->routeIs('workload.*')) aria-current="page" @endif
                >
                    Workload
                </a>

                <a
                    href="{{ route('analytics.index') }}"
                    class="{{ request()->routeIs('analytics.*') ? 'ff-active' : '' }}"
                    @if (request()->routeIs('analytics.*')) aria-current="page" @endif
                >
                    Analytics
                </a>

                <a
                    href="{{ route('properties.index') }}"
                    class="{{ request()->routeIs('properties.*') ? 'ff-active' : '' }}"
                    @if (request()->routeIs('properties.*')) aria-current="page" @endif
                >
                    Properties
                </a>
            @endif

            @if ($isAdmin)
                <a
                    href="{{ route('team.index') }}"
                    class="{{ request()->routeIs('team.*') ? 'ff-active' : '' }}"
                    @if (request()->routeIs('team.*')) aria-current="page" @endif
                >
                    Team
                </a>
            @endif

            @if ($isTechnician)
                <a
                    href="{{ route('technician.index') }}"
                    class="{{ request()->routeIs('technician.*') ? 'ff-active' : '' }}"
                    @if (request()->routeIs('technician.*')) aria-current="page" @endif
                >
                    My Jobs
                </a>
            @endif

            <div class="ff-account">

                <span
                    class="ff-role {{ $roleClass }}"
                    title="{{ $user?->name ?? 'Current account' }}"
                >
                    <span class="ff-role-dot"></span>
                    {{ $roleLabel }}
                </span>

                <form
                    action="{{ route('logout') }}"
                    method="POST"
                    style="margin: 0;"
                >
                    @csrf

                    <button type="submit" class="ff-logout">
                        Log out
                    </button>
                </form>

            </div>

        </div>
    </div>
</nav>

<script>
    (function () {
        const toggle = document.getElementById('ff-mobile-toggle');
        const links = document.getElementById('ff-nav-links');

        if (!toggle || !links) {
            return;
        }

        function setMenuOpen(isOpen) {
            links.classList.toggle('ff-mobile-open', isOpen);

            toggle.setAttribute(
                'aria-expanded',
                isOpen ? 'true' : 'false'
            );

            toggle.setAttribute(
                'aria-label',
                isOpen
                    ? 'Close navigation menu'
                    : 'Open navigation menu'
            );

            toggle.innerHTML = isOpen
                ? '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"></path></svg>'
                : '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"></path></svg>';
        }

        toggle.addEventListener('click', function () {
            const isOpen = toggle.getAttribute('aria-expanded') === 'true';

            setMenuOpen(!isOpen);
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                setMenuOpen(false);
            }
        });

        links.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                setMenuOpen(false);
            });
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth > 950) {
                setMenuOpen(false);
            }
        });
    })();
</script>
