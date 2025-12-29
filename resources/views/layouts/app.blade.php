<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'My Campus')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] min-h-screen">

    <!-- Main content -->
    <div id="main-content">
        @yield('content')
    </div>

    <!-- Communication Hub Floating Button -->
    @auth
        @include('communicationhub.hub')
    @endauth

</body>
</html>
