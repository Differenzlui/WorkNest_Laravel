@extends('layouts.guest')

@section('title', 'Reset password - WorkNest')

@section('content')
    <div class="rounded-3xl bg-cream p-8">
        <h1 class="mb-4 text-center text-xl font-bold">Choose a new password</h1>

        <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div>
                <input type="email" name="email" value="{{ old('email', $email) }}" placeholder="Email" required
                       class="w-full rounded-2xl bg-white px-5 py-4 placeholder-gray-400 outline-none focus:ring-2 focus:ring-leaf">
                @error('email')
                    <p class="mt-1 px-2 text-sm text-red-700">{{ $message }}</p>
                    <a href="{{ route('password.request') }}" class="mt-1 inline-block px-2 text-sm underline">
                        Request a new link
                    </a>
                @enderror
            </div>

            <div>
                <x-password-input name="password" placeholder="New password (at least 8 characters)" autocomplete="new-password" />
                @error('password')
                    <p class="mt-1 px-2 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-password-input name="password_confirmation" placeholder="Confirm new password" autocomplete="new-password" />
                <p id="match-hint" class="mt-1 px-2 text-sm"></p>
            </div>

            <button type="submit"
                    class="mt-2 w-full rounded-full bg-bark py-3 text-lg font-bold text-cream hover:opacity-90">
                Reset password
            </button>
        </form>
    </div>

    <script>
        // Live hint under "Confirm new password" (the real check happens on the server)
        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('password_confirmation');
        const matchHint = document.getElementById('match-hint');

        function checkMatch() {
            if (confirmInput.value === '') {
                matchHint.textContent = '';
                return;
            }

            const ok = passwordInput.value === confirmInput.value;
            matchHint.textContent = ok ? 'Passwords match' : 'Passwords do not match yet';
            matchHint.className = 'mt-1 px-2 text-sm ' + (ok ? 'text-green-700' : 'text-red-700');
        }

        passwordInput.addEventListener('input', checkMatch);
        confirmInput.addEventListener('input', checkMatch);
    </script>
@endsection