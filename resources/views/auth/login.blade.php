
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | FixFlow</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background: #f5f5f7;
            font-family: -apple-system, BlinkMacSystemFont,
                         "Segoe UI", sans-serif;
            color: #1d1d1f;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            padding: 42px;
            background: #ffffff;
            border: 1px solid #e5e5e7;
            border-radius: 22px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.05);
        }

        .brand {
            font-size: 30px;
            font-weight: 750;
            letter-spacing: -1.2px;
            color: #0071e3;
            margin-bottom: 30px;
        }

        h1 {
            font-size: 27px;
            letter-spacing: -0.7px;
            margin: 0 0 10px;
        }

        .subtitle {
            color: #86868b;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 9px;
        }

        input {
            width: 100%;
            padding: 14px 15px;
            border: 1px solid #d2d2d7;
            border-radius: 10px;
            font-size: 15px;
            margin-bottom: 20px;
            outline: none;
            background: #fff;
        }

        input:focus {
            border-color: #0071e3;
            box-shadow: 0 0 0 3px rgba(0, 113, 227, 0.1);
        }

        button {
            width: 100%;
            padding: 15px;
            background: #0071e3;
            border: none;
            border-radius: 11px;
            color: white;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
        }

        button:hover {
            background: #0077ed;
        }

        .error-box {
            padding: 13px;
            margin-bottom: 22px;
            background: #fff1f0;
            color: #c62828;
            border-radius: 10px;
            font-size: 13px;
        }

        .footer {
            text-align: center;
            color: #86868b;
            font-size: 12px;
            margin-top: 28px;
        }
    </style>
</head>

<body>
    <main class="login-card">
        <div class="brand">FixFlow.</div>

        <h1>Welcome back</h1>

        <p class="subtitle">
            Sign in to manage your properties,
            maintenance requests, and team.
        </p>

        @if ($errors->any())
            <div class="error-box" role="alert">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login.submit') }}">
            @csrf

            <label for="email">Email address</label>
            <input
                type="email"
                id="email"
                name="email"
                value="{{ old('email') }}"
                placeholder="you@company.com"
                autocomplete="email"
                required
                autofocus
            >

            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                placeholder="Enter your password"
                autocomplete="current-password"
                required
            >

            <button type="submit">Sign In</button>
        </form>

        <div class="footer">
            FixFlow Property Management Platform
        </div>
    </main>
</body>
</html>
