<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @stack('meta')
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=noto-sans:400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css'])
        @stack('schema')
        <title>@yield('title', config('app.name'))</title>
    </head>
    <body class="mom-body font-sans antialiased text-[var(--text-primary)]">
        <div class="mom-noise" aria-hidden="true"></div>
        <div class="relative z-10 min-h-screen">
            @yield('content')
        </div>
        @stack('scripts')
    </body>
</html>
