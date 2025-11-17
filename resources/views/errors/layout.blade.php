<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title') - {{ config('app.name') }}</title>

        <!-- Styles -->
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">
        
        <style>
            body {
                font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
                background-image: url('{{ asset('images/bg-space1.png') }}');
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
                color: #fff;
            }
            .error-container {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                text-align: center;
                padding: 0 1rem;
            }
            .error-code {
                font-size: 8rem;
                font-weight: bold;
                margin-bottom: 1rem;
                color: #f59e0b;
                text-shadow: 0 0 10px rgba(245, 158, 11, 0.5);
            }
            .error-message {
                font-size: 2rem;
                margin-bottom: 2rem;
                color: #f3f4f6;
            }
            .error-description {
                font-size: 1.2rem;
                margin-bottom: 2rem;
                max-width: 600px;
                color: #d1d5db;
            }
            .back-button {
                display: inline-block;
                background-color: #f59e0b;
                color: #1f2937;
                font-weight: bold;
                padding: 0.75rem 1.5rem;
                border-radius: 0.375rem;
                text-decoration: none;
                transition: all 0.3s ease;
            }
            .back-button:hover {
                background-color: #d97706;
                transform: translateY(-2px);
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }
            .logo {
                max-width: 200px;
                margin-bottom: 2rem;
            }
        </style>
    </head>
    <body>
        <div class="error-container">
            <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name') }} Logo" class="logo">
            
            <div class="error-code">@yield('code')</div>
            
            <div class="error-message">@yield('message')</div>
            
            <div class="error-description">@yield('description')</div>
            
            <a href="{{ url('/') }}" class="back-button">Retour à l'accueil</a>
        </div>
    </body>
</html>