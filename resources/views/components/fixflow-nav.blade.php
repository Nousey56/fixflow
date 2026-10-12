
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
        background: rgba(255, 255, 255, 0.92);
        border-bottom: 1px solid #e5e5ea;
        position: sticky;
        top: 0;
        z-index: 100;
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
    }

    .ff-nav-inner {
        max-width: 1100px;
        margin: 0 auto;
        min-height: 64px;
        padding: 12px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
    }

    .ff-brand {
        font-size: 22px;
        font-weight: 750;
        letter-spacing: -0.8px;
        color: #0071e3;
        text-decoration: none;
    }

    .ff-nav-links {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 22px;
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

    @media (max-width: 850px) {
        .ff-nav-inner {
            align-items: flex-start;
            flex-direction: column;
            gap: 14px;
        }

        .ff-nav-links {
            gap: 14px;
        }

        .ff-account {
            padding-left: 0;
            border-left: 0;
        }
    }
</style>

<nav class="ff-navbar" aria-label="Main navigation">
    <div class="ff-nav-inner">

        <a class="ff-brand" href="{{ route('maintenance.index') }}">
            FixFlow.
        </a>

        <div class="ff-nav-links">

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
                    href="{{ route('properties.index') }}"
                    class="{{ request()->routeIs('properties.*') ? 'ff-active' : '' }}"
                >
                    Properties
                </a>
            @endif

            @if ($isAdmin)
                <a
                    href="{{ route('team.index') }}"
                    class="{{ request()->routeIs('team.*') ? 'ff-active' : '' }}"
                >
                    Team
                </a>
            @endif

            @if ($isTechnician)
                <a
                    href="{{ route('technician.index') }}"
                    class="{{ request()->routeIs('technician.*') ? 'ff-active' : '' }}"
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
