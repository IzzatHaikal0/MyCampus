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
        
        @auth
        
    {{-- Floating Hub Launcher --}}
    <button id="openHub" class="fixed bottom-6 right-6 z-50 p-2 bg-white rounded-full shadow-lg hover:scale-110 transition transform">
        <img src="{{ asset('images/chat-icon.jpg') }}" alt="Hub" class="h-10 w-10">
    </button>

    {{-- Include the full Communication Hub --}}
    @include('CommunicationHub.hub')
@endauth

    </div>

    <!-- Communication Hub Floating Button -->
    @auth
        @include('CommunicationHub.hub')
    @endauth

    <!-- Push scripts here -->
    @stack('scripts')
</body>
</html>
