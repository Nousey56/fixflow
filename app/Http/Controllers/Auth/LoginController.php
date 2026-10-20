<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => 'The provided credentials are incorrect.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(
            route('maintenance.index')
        );
    }

    public function demoLogin(Request $request)
    {
        $demoUser = User::query()
            ->where('email', 'demo@fixflow.com')
            ->whereHas('memberships', function ($query) {
                $query->where('role', 'demo_viewer')
                    ->where('status', 'active');
            })
            ->first();

        if (! $demoUser) {
            return redirect()
                ->route('login')
                ->withErrors([
                    'demo' => 'The demonstration account is currently unavailable.',
                ]);
        }

        Auth::login($demoUser);

        $request->session()->regenerate();

        return redirect()->route('maintenance.index');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
