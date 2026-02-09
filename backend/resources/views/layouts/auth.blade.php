<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'PGFE') }} – Connexion</title>
    @vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body class="min-h-screen h-full bg-gradient-to-br from-violet-50 via-white to-fuchsia-50 text-gray-800 antialiased">
    <div class="app-container min-h-screen flex flex-col">
        <main class="flex-1 flex">@yield('content')</main>
    </div>
    @stack('scripts')
</body>
</html>

