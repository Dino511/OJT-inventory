<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Render Login View
    public function showLogin()
    {
        return view('auth.login');
    }

    // Process Login Request
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            if (! Auth::user()->isActive()) {
                Auth::logout();

                return back()->withErrors([
                    'email' => 'This account has been deactivated. Contact an administrator.',
                ])->onlyInput('email');
            }

            if (Auth::user()->isModerator()) {
                Auth::logout();

                return back()->withErrors([
                    'email' => 'This account only has access to the POS system.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();
            return redirect()->intended(route('dashboard.index'))
                ->with('success', 'Welcome back!');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    // Render Sign Up View
    public function showRegister()
    {
        return view('auth.register');
    }

    // Process Sign Up Request
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'               => ['required', 'string', 'max:255'],
            'email'              => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'alternative_email'  => ['nullable', 'string', 'email', 'max:255', 'different:email'],
            'password'           => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name'              => $validated['name'],
            'email'             => $validated['email'],
            'alternative_email' => $validated['alternative_email'] ?? null,
            'password'          => Hash::make($validated['password']),
            'role'              => User::ROLE_MODERATOR,
        ]);

        return redirect()->route('login')
            ->with('success', 'Account created successfully! An administrator must grant you access before you can sign in here — you can already sign in to POS.');
    }

    // Process Logout Request
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'Logged out successfully.');
    }
}