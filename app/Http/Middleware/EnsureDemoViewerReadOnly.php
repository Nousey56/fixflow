<?php

namespace App\Http\Middleware;

use App\Models\CompanyMembership;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDemoViewerReadOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        $roles = CompanyMembership::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->pluck('role');

        $isDemoViewerOnly = $roles->isNotEmpty()
            && $roles->every(
                fn ($role) => $role === 'demo_viewer'
            );

        if (
            $isDemoViewerOnly
            && ! $request->isMethod('GET')
            && ! $request->isMethod('HEAD')
            && ! $request->isMethod('OPTIONS')
            && ! $request->routeIs('logout')
        ) {
            abort(403, 'Demo viewers have read-only access.');
        }

        return $next($request);
    }
}
