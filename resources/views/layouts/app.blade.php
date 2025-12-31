<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', config('app.name', 'MyCampus'))</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#FDFDFC] dark:bg-[#0a0a0a] text-[#1b1b18] min-h-screen font-sans antialiased">

    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        
        {{-- Top Navigation (from ManageAssignment) --}}
        @include('layouts.navigation')

        {{-- Main Page Content --}}
        <main id="main-content">
            @yield('content')
        </main>

        {{-- Communication Hub + Floating Button (only for logged-in users) --}}
        @auth
            {{-- Floating Hub Launcher --}}
            <button id="openHub"
                class="fixed bottom-6 right-6 z-50 p-2 bg-white rounded-full shadow-lg hover:scale-110 transition transform">
                <img src="{{ asset('images/chat-icon.jpg') }}" alt="Hub" class="h-10 w-10">
            </button>

            {{-- Full Communication Hub --}}
            @include('CommunicationHub.hub')
        @endauth
    </div>

    {{-- Extra page scripts --}}
    @stack('scripts')

    {{-- External scripts --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>
