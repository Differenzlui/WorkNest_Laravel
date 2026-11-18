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
<body class="flex min-h-screen items-center justify-center bg-sage text-bark">

    <div class="mx-auto flex w-full max-w-5xl flex-col items-center gap-10 px-6 py-10 md:flex-row md:justify-around">
        <img src="{{ asset('images/logo.png') }}" alt="WorkNest" class="w-56 md:w-80">

        <div class="w-full max-w-md">
            @yield('content')
        </div>
    </div>

</body>
</html>