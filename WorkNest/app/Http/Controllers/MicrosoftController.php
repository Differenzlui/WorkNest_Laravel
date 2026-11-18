<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class MicrosoftController extends Controller
{
    // Step 1: send the visitor to Microsoft's login page
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('microsoft')->redirect();
    }

    // Step 2: Microsoft sends the visitor back here after login
    public function callback(Request $request): RedirectResponse
    {
        // The visitor pressed Cancel, or Microsoft reported a problem
        if ($request->has('error')) {
            return $this->fail('Microsoft sign-in was cancelled or failed. Please try again.');
        }

        try {
            $microsoftUser = Socialite::driver('microsoft')->user();
        } catch (\Throwable $e) {
            report($e);

            return $this->fail('Could not sign in with Microsoft. Please try again.');
        }

        // Find the person's email
        $raw = (array) $microsoftUser->user;
        $email = strtolower((string) (
            $microsoftUser->getEmail() ?? $raw['mail'] ?? $raw['userPrincipalName'] ?? ''
        ));

        if ($email === '') {
            return $this->fail('Microsoft did not share an email address for this account.');
        }

        // Optional: only allow school emails (set in .env)
        $allowedDomain = strtolower(ltrim((string) config('services.microsoft.allowed_domain'), '@'));

        if ($allowedDomain !== '' && ! str_ends_with($email, '@' . $allowedDomain)) {
            return $this->fail('Please sign in with your MCC Microsoft account.');
        }

        // Find the user: by Microsoft ID first, then by email
        $user = User::where('microsoft_id', $microsoftUser->getId())->first()
            ?? User::where('email', $email)->first();

        if ($user) {
            // Existing account: connect it to Microsoft if it isn't yet
            if (! $user->microsoft_id) {
                $user->forceFill(['microsoft_id' => $microsoftUser->getId()])->save();
            }
        } else {
            // First time here: create a new account
            $user = new User();
            $user->forceFill([
                'name' => $microsoftUser->getName() ?: $email,
                'email' => $email,
                'microsoft_id' => $microsoftUser->getId(),
                'email_verified_at' => now(),
            ])->save();
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended('/tasks');
    }

    private function fail(string $message): RedirectResponse
    {
        return redirect()->route('login')->withErrors(['email' => $message]);
    }
}