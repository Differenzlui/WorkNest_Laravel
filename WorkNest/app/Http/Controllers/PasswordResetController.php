<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    // Page 1: the "Forgot your password?" form
    public function showForgot(): View
    {
        return view('auth.forgot-password');
    }

    // Create a one-time link and email it
    public function sendLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        // Laravel finds the user, makes a secret token and emails the link
        Password::sendResetLink($request->only('email'));

        // Same answer whether or not the email has an account, so this form
        // can't be used to find out who is registered.
        return back()->with(
            'status',
            'If that email has an account, we have sent a link to reset the password. Check your inbox and spam folder.'
        );
    }

    // Page 2: the "Choose a new password" form (opened from the email link)
    public function showReset(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    // Save the new password
    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')
                ->with('status', 'Your password has been reset. You can log in now.');
        }

        // Bad or expired link, or the email doesn't match the link
        return back()
            ->withInput($request->only('email'))
            ->withErrors(['email' => __($status)]);
    }
}