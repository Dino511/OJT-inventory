<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActive
{
    /**
     * Handle an incoming request.
     *
     * Catches two account changes that can happen while a user is already
     * mid-session, on their very next request, instead of waiting for them
     * to log out and back in (which would otherwise be the only place
     * either check ran — see AuthController::login):
     *  - a Super Admin deactivates the account;
     *  - a Super Admin demotes the account to Moderator, who — per
     *    User::canAccessInventoryApp() — have no access to this back
     *    office at all and are POS-only.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && ! Auth::user()->isActive()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'This account has been deactivated. Contact an administrator.',
            ]);
        }

        if (Auth::check() && ! Auth::user()->canAccessInventoryApp()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'This account only has access to the POS system.',
            ]);
        }

        return $next($request);
    }
}
