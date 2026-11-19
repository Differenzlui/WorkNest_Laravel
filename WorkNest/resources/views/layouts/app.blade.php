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
            <a href="{{ route('tasks.index') }}">
                <img src="{{ asset('images/logo-horizontal.png') }}" alt="WorkNest" class="h-10 sm:h-12">
            </a>

            <nav class="flex items-center gap-4 font-bold sm:gap-8">
                <a href="{{ route('tasks.index') }}"
                   class="hover:underline {{ request()->is('tasks*') ? 'underline' : '' }}">Home</a>
                <a href="{{ route('calendar') }}"
                   class="hover:underline {{ request()->is('calendar*') ? 'underline' : '' }}">Calendar</a>
                <a href="{{ route('groups.index') }}"
                   class="hover:underline {{ request()->is('groups*') ? 'underline' : '' }}">Groups</a>

                {{-- Profile dropdown --}}
                <div class="relative">
                    <button type="button" id="profile-button"
                            aria-haspopup="true" aria-expanded="false" aria-controls="profile-menu"
                            class="flex items-center gap-1 hover:underline">
                        Profile
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </button>

                    <div id="profile-menu"
                         class="absolute right-0 top-full z-50 mt-2 hidden w-64 max-w-[calc(100vw-2rem)] rounded-3xl bg-bark p-4 font-normal text-bark shadow-lg">
                        <p class="truncate rounded-lg bg-cream px-3 py-2 font-bold" title="{{ auth()->user()->name }}">
                            {{ auth()->user()->name }}
                        </p>
                        <p class="mt-2 truncate rounded-lg bg-cream px-3 py-2 text-sm" title="{{ auth()->user()->email }}">
                            {{ auth()->user()->email }}
                        </p>

                        <form method="POST" action="{{ route('logout') }}" class="mt-4">
                            @csrf
                            <button type="submit"
                                    class="w-full rounded-full bg-leaf py-2 font-bold hover:brightness-95">
                                Log Out
                            </button>
                        </form>
                    </div>
                </div>
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

    <script>
        const profileButton = document.getElementById('profile-button');
        const profileMenu = document.getElementById('profile-menu');

        function setProfileMenu(open) {
            profileMenu.classList.toggle('hidden', !open);
            profileButton.setAttribute('aria-expanded', open ? 'true' : 'false');
        }

        // Click "Profile" to open or close the menu
        profileButton.addEventListener('click', (event) => {
            event.stopPropagation();
            setProfileMenu(profileMenu.classList.contains('hidden'));
        });

        // Click anywhere outside the menu to close it
        document.addEventListener('click', (event) => {
            if (!profileMenu.contains(event.target)) {
                setProfileMenu(false);
            }
        });

        // Press Esc to close it
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                setProfileMenu(false);
            }
        });
    </script>

</body>
</html>