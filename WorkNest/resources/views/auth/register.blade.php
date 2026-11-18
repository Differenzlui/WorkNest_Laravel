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
                <input type="password" name="password" placeholder="Password (at least 8 characters)" required
                       class="w-full rounded-2xl bg-white px-5 py-4 placeholder-gray-400 outline-none focus:ring-2 focus:ring-leaf">
                @error('password')
                    <p class="mt-1 px-2 text-sm text-red-700">{{ $message }}</p>
                @enderror
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
@endsection