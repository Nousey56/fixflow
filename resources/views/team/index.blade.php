
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Team Management | FixFlow</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #f5f5f7;
            color: #1d1d1f;
        }

        a {
            text-decoration: none;
        }

        .container {
            max-width: 1100px;
            margin: 65px auto;
            padding: 0 24px;
        }

        .eyebrow {
            color: #0071e3;
            font-weight: 700;
            font-size: 13px;
        }

        h1 {
            font-size: clamp(36px, 5vw, 54px);
            letter-spacing: -2px;
            margin: 12px 0;
        }

        .subtitle {
            color: #777;
            font-size: 17px;
            margin-bottom: 35px;
        }

        .card {
            background: #fff;
            border: 1px solid #e9e9ed;
            border-radius: 22px;
            padding: 30px;
            margin-bottom: 25px;
        }

        h2 {
            font-size: 23px;
            margin: 0 0 10px;
            letter-spacing: -.5px;
        }

        .muted {
            color: #777;
            font-size: 14px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        input, select {
            width: 100%;
            border: 1px solid #d7d7dc;
            border-radius: 10px;
            padding: 13px;
            font-size: 14px;
            background: white;
            color: #1d1d1f;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #0071e3;
            box-shadow: 0 0 0 3px #0071e320;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr auto;
            gap: 15px;
            align-items: end;
            margin-top: 25px;
        }

        button {
            cursor: pointer;
            font-weight: 600;
            border-radius: 10px;
            border: 0;
            padding: 13px 18px;
            white-space: nowrap;
        }

        .primary {
            background: #0071e3;
            color: white;
        }

        .secondary {
            background: #f0f0f2;
            color: #333;
        }

        .danger {
            background: #fff0f0;
            color: #c62828;
        }

        .alert {
            border-radius: 12px;
            padding: 15px 18px;
            margin-bottom: 22px;
            font-size: 14px;
        }

        .success {
            background: #eaf8ee;
            color: #206a35;
        }

        .error {
            background: #fff0f0;
            color: #a52222;
        }

        .member {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            padding: 22px 0;
            border-bottom: 1px solid #ededf0;
        }

        .member:last-child {
            border-bottom: 0;
        }

        .member-info {
            display: flex;
            gap: 15px;
            align-items: center;
            min-width: 0;
        }

        .avatar {
            width: 46px;
            height: 46px;
            border-radius: 50%;
            background: #e8f2ff;
            color: #0071e3;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            flex-shrink: 0;
        }

        .member-name {
            font-weight: 700;
            margin-bottom: 5px;
        }

        .member-email {
            font-size: 13px;
            color: #777;
            overflow-wrap: anywhere;
        }

        .member-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .member-actions select {
            min-width: 155px;
        }

        .role-form {
            display: flex;
            gap: 8px;
        }

        .status {
            font-size: 12px;
            padding: 6px 10px;
            border-radius: 30px;
            background: #eaf8ee;
            color: #28783b;
            text-transform: capitalize;
        }

        .empty {
            padding: 35px 0;
            color: #777;
            text-align: center;
        }

        @media (max-width: 760px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .member {
                align-items: stretch;
                flex-direction: column;
            }

            .member-actions {
                padding-left: 0;
            }

            .role-form {
                flex-wrap: wrap;
            }
        }
    </style>
</head>

<body>
    <x-fixflow-nav />

    <main class="container">
        <div class="eyebrow">WORKSPACE MANAGEMENT</div>

        <h1>Your team.</h1>

        <p class="subtitle">
            Manage the people who keep your properties running.
        </p>

        @if (session('success'))
            <div class="alert success">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert error">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <section class="card">
            <h2>Add a team member</h2>

            <p class="muted">
                Add an existing FixFlow user to your company.
            </p>

            <form method="POST" action="{{ route('team.store') }}">
                @csrf

                <div class="form-grid">
                    <div>
                        <label for="email">Email address</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            placeholder="teammate@example.com"
                            required
                        >
                    </div>

                    <div>
                        <label for="role">Role</label>
                        <select name="role" id="role" required>
                            <option value="technician" @selected(old('role') === 'technician')>
                                Technician
                            </option>
                            <option value="manager" @selected(old('role') === 'manager')>
                                Manager
                            </option>
                            <option value="company_admin" @selected(old('role') === 'company_admin')>
                                Company Administrator
                            </option>
                        </select>
                    </div>

                    <button class="primary" type="submit">
                        Add Member
                    </button>
                </div>
            </form>
        </section>

        <section class="card">
            <h2>Team members</h2>

            <p class="muted">
                View and manage your company's members.
            </p>

            @forelse ($members as $membership)
                <div class="member">
                    <div class="member-info">
                        <div class="avatar">
                            {{ strtoupper(substr($membership->user->name ?? '?', 0, 1)) }}
                        </div>

                        <div>
                            <div class="member-name">
                                {{ $membership->user->name ?? 'Unknown User' }}
                            </div>

                            <div class="member-email">
                                {{ $membership->user->email ?? 'No email available' }}
                            </div>

                            <div style="margin-top: 8px;">
                                <span class="status">
                                    {{ str_replace('_', ' ', $membership->status) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="member-actions">
                        <form
                            class="role-form"
                            method="POST"
                            action="{{ route('team.update', $membership) }}"
                        >
                            @csrf
                            @method('PATCH')

                            <select name="role" aria-label="Member role">
                                <option value="technician" @selected($membership->role === 'technician')>
                                    Technician
                                </option>
                                <option value="manager" @selected($membership->role === 'manager')>
                                    Manager
                                </option>
                                <option value="company_admin" @selected($membership->role === 'company_admin')>
                                    Company Administrator
                                </option>
                            </select>

                            <button class="secondary" type="submit">
                                Save
                            </button>
                        </form>

                        <form
                            method="POST"
                            action="{{ route('team.destroy', $membership) }}"
                            onsubmit="return confirm('Remove this member from your company?');"
                        >
                            @csrf
                            @method('DELETE')

                            <button class="danger" type="submit">
                                Remove
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="empty">
                    No team members found.
                </div>
            @endforelse
        </section>
    </main>
</body>
</html>
