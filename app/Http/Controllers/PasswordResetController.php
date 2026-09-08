<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\PasswordResetCodeNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PasswordResetController extends Controller
{
    private const CODE_EXPIRY_MINUTES = 15;

    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    public function sendCode(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if ($user) {
            $code = (string) random_int(100000, 999999);

            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                ['token' => Hash::make($code), 'created_at' => now()]
            );

            $user->notify(new PasswordResetCodeNotification($code));
        }

        return redirect()->route('password.reset.form', ['email' => $validated['email']])
            ->with('success', 'If an account exists for that email, a reset code has been sent to its recovery address.');
    }

    public function showResetForm(Request $request)
    {
        return view('auth.reset-password', ['email' => $request->query('email')]);
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'code' => ['required', 'digits:6'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $record = DB::table('password_reset_tokens')->where('email', $validated['email'])->first();

        $valid = $record
            && now()->diffInMinutes($record->created_at) <= self::CODE_EXPIRY_MINUTES
            && Hash::check($validated['code'], $record->token);

        if (! $valid) {
            return back()->withErrors(['code' => 'Invalid or expired code.'])->onlyInput('email');
        }

        User::where('email', $validated['email'])->update([
            'password' => Hash::make($validated['password']),
        ]);

        DB::table('password_reset_tokens')->where('email', $validated['email'])->delete();

        return redirect()->route('login')->with('success', 'Password reset successfully. You can now sign in.');
    }
}
