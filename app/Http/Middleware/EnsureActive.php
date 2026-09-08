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
     * A Super Admin can deactivate an account while it's mid-session; this
     * catches that on the deactivated user's very next request instead of
     * waiting for them to log out and back in.
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

        return $next($request);
    }
}
