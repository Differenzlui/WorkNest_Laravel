@extends('layouts.guest')

@section('title', 'Log in - WorkNest')

@section('content')
    <div class="rounded-3xl bg-cream p-8">
        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <div>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="Email" required autofocus
                       class="w-full rounded-2xl bg-white px-5 py-4 placeholder-gray-400 outline-none focus:ring-2 focus:ring-leaf">
                @error('email')
                    <p class="mt-1 px-2 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <input type="password" name="password" placeholder="Password" required
                       class="w-full rounded-2xl bg-white px-5 py-4 placeholder-gray-400 outline-none focus:ring-2 focus:ring-leaf">
                @error('password')
                    <p class="mt-1 px-2 text-sm text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                    class="mt-2 w-full rounded-full bg-leaf py-3 text-lg font-bold hover:brightness-95">
                Log in
            </button>
        </form>

                <div class="my-4 flex items-center gap-3 text-sm opacity-60">
            <div class="h-px flex-1 bg-bark"></div>
            <span>or</span>
            <div class="h-px flex-1 bg-bark"></div>
        </div>

        <a href="{{ route('microsoft.redirect') }}"
           class="flex w-full items-center justify-center gap-3 rounded-full bg-white py-3 text-lg font-bold hover:brightness-95">
            <svg class="h-5 w-5" viewBox="0 0 21 21" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <rect x="1" y="1" width="9" height="9" fill="#f25022"/>
                <rect x="11" y="1" width="9" height="9" fill="#7fba00"/>
                <rect x="1" y="11" width="9" height="9" fill="#00a4ef"/>
                <rect x="11" y="11" width="9" height="9" fill="#ffb900"/>
            </svg>
            Sign in with Microsoft
        </a>

        <a href="{{ route('register') }}"
           class="mt-4 block w-full rounded-full bg-bark py-3 text-center text-lg font-bold text-cream hover:opacity-90">
            Create new account
        </a>
    </div>
@endsection