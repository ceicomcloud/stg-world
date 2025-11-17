<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name') }}</title>

    @include('components.partials.seo')

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
        
    <!-- Custom Styles -->
    @stack('styles')
</head>
<body>
    @auth
        <livewire:dashboard.navbar />
    @endauth
    
    @guest
        @if(request()->routeIs('login') || request()->routeIs('register') || request()->routeIs('forgot-password'))
            <div class="auth-container">
                <livewire:auth.auth-extra />
                {{ $slot }}
            </div>
        @else
            {{ $slot }}
        @endif
    @else
        {{ $slot }}
    @endguest

    @livewireScriptConfig 
</body>
</html>