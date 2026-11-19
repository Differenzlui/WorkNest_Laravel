@extends('layouts.guest')

@section('title', 'Forgot password - WorkNest')

@section('content')
    <div class="rounded-3xl bg-cream p-8">
        <h1 class="text-center text-xl font-bold">Forgot your password?</h1>
        <p class="mt-2 text-center text-sm opacity-80">
            Enter your email and we'll send you a link to choose a new one.
        </p>

        @if (session('status'))
            <div class="mt-4 rounded-2xl bg-leaf px-4 py-3 text-sm font-bold">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="mt-4 space-y-4">
            @csrf

            <div>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="Email" required autofocus
                       class="w-full rounded-2xl bg-white px-5 py-4 placeholder-gray-400 outline-none focus:ring-2 focus:ring-leaf">
                @error('email')
                    <p class="mt-1 px-2 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                    class="w-full rounded-full bg-bark py-3 text-lg font-bold text-cream hover:opacity-90">
                Send reset link
            </button>
        </form>

        <p class="mt-4 text-center text-xs opacity-70">
            Do you sign in with Microsoft? Use the Microsoft button on the log in page instead.
        </p>

        <a href="{{ route('login') }}" class="mt-3 block text-center text-sm underline">Back to log in</a>
    </div>
@endsection