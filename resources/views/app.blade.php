<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="user" content="{{ Auth::user() ? Auth::user()->id : '' }}">
    <meta name="user-role" content="{{ Auth::user() ? Auth::user()->roles->first()->name ?? 'employee' : '' }}">

    <title>{{ config('app.name', 'GOHR') }} - HR Management System</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <div id="app"></div>
    
    <script>
        // Pass CSRF token to Vue.js app
        window.csrfToken = '{{ csrf_token() }}';
        
        // Pass user data if authenticated
        @if(Auth::check())
        window.userData = {
            id: {{ Auth::user()->id }},
            name: '{{ Auth::user()->name }}',
            email: '{{ Auth::user()->email }}',
            role: '{{ Auth::user()->roles->first()->name ?? 'employee' }}',
            organization_id: {{ Auth::user()->organization_id ?? 'null' }}
        };
        @else
        window.userData = null;
        @endif
    </script>
</body>
</html> 