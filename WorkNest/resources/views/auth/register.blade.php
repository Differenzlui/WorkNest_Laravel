@extends('layouts.guest')

@section('title', 'Create account - WorkNest')

@section('content')
    <div class="rounded-3xl bg-cream p-8">
        <h1 class="mb-4 text-center text-xl font-bold">Create new account</h1>

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf

            <div>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="Username" required autofocus
                       class="w-full rounded-2xl bg-white px-5 py-4 placeholder-gray-400 outline-none focus:ring-2 focus:ring-leaf">
                @error('name')
                    <p class="mt-1 px-2 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="Email" required
                       class="w-full rounded-2xl bg-white px-5 py-4 placeholder-gray-400 outline-none focus:ring-2 focus:ring-leaf">
                @error('email')
                    <p class="mt-1 px-2 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-password-input name="password" placeholder="Password (at least 8 characters)" autocomplete="new-password" />
                @error('password')
                    <p class="mt-1 px-2 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-password-input name="password_confirmation" placeholder="Confirm password" autocomplete="new-password" />
                <p id="match-hint" class="mt-1 px-2 text-sm"></p>
            </div>

            <button type="submit"
                    class="mt-2 w-full rounded-full bg-bark py-3 text-lg font-bold text-cream hover:opacity-90">
                Create
            </button>
        </form>

        <a href="{{ route('login') }}" class="mt-4 block text-center text-sm underline">
            I already have an account
        </a>
    </div>

    <script>
        // Live hint under "Confirm password" (the real check happens on the server)
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