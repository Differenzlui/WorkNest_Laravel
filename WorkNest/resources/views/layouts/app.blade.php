<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'WorkNest')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-sage text-bark">

    {{-- Header --}}
    <header class="bg-leaf">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-3">
            <a href="{{ url('/tasks') }}">
                <img src="{{ asset('images/logo-horizontal.png') }}" alt="WorkNest" class="h-12">
            </a>

            <nav class="flex items-center gap-8 font-bold">
                <a href="{{ url('/tasks') }}"
                   class="hover:underline {{ request()->is('tasks*') ? 'underline' : '' }}">Home</a>
                <a href="{{ url('/calendar') }}"
                   class="hover:underline {{ request()->is('calendar*') ? 'underline' : '' }}">Calendar</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="font-bold hover:underline">Log out</button>
                </form>
            </nav>
        </div>
    </header>

    {{-- Page content goes here --}}
    <main class="mx-auto w-full max-w-6xl flex-1 px-6 py-8">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-bark py-8 text-center text-sm text-cream">
        &copy; {{ date('Y') }} WorkNest
    </footer>

</body>
</html>