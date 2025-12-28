<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @isset($header)
            <header class="bg-white dark:bg-gray-800 shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>
    </div>

    <button id="chatFab" class="chat-fab">
        <img src="{{ asset('images/chat-icon.png') }}" alt="Chat">
        <span id="chatBadge" class="chat-badge">1</span>
    </button>

    <div id="chatHub" class="chat-hub hidden">
        <div class="chat-hub-header">
            <h4>Communication Hub</h4>
            <button onclick="toggleHub()">✕</button>
        </div>

        <div class="class-list">
            <div class="class-item" onclick="openClassChat()">
                Mathematics 101
                <span class="chat-badge">1</span>
            </div>
        </div>
    </div>

    <div id="classChat" class="class-chat hidden">
        <div class="chat-header">
            <h4>Mathematics 101</h4>

            @if(auth()->user()->role === 'teacher')
                <button onclick="openAnnouncement()" class="announce-btn">
                    + Announcement
                </button>
            @endif
        </div>

        <div class="chat-messages">
            <div class="msg teacher">Submit by Friday.</div>
            <div class="msg student">Okay sir.</div>
        </div>

        <div class="chat-input">
            <input type="text" placeholder="Type message..." />
            <button>Send</button>
        </div>
    </div>

    <div id="announcementModal" class="announcement-modal hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
        <div class="announcement-form bg-white p-6 rounded-lg w-96">
            <h2>Announcements</h2>

            <div id="announcementList">
                <div class="announcement-item border p-3 mb-2 rounded">
                    <h4>Submit Homework</h4>
                    <p>Due by Friday</p>
                    <small>2025-12-29</small>

                    @if(auth()->user()->role === 'teacher')
                        <div class="actions mt-2">
                            <button onclick="editAnnouncement(this)" class="px-2 py-1 bg-yellow-400 rounded">Edit</button>
                            <button onclick="deleteAnnouncement(this)" class="px-2 py-1 bg-red-500 text-white rounded">Delete</button>
                        </div>
                    @endif
                </div>
            </div>

            @if(auth()->user()->role === 'teacher')
                <form id="createAnnouncementForm" class="mt-4 flex flex-col gap-2">
                    <input type="text" placeholder="Task title" required class="border p-2 rounded">
                    <textarea placeholder="Task detail" required class="border p-2 rounded"></textarea>
                    <input type="date" required class="border p-2 rounded">

                    <div class="actions mt-2 flex gap-2">
                        <button type="submit" class="px-3 py-1 bg-blue-500 text-white rounded">Create</button>
                        <button type="button" onclick="closeAnnouncement()" class="px-3 py-1 bg-gray-300 rounded">Cancel</button>
                    </div>
                </form>
            @else
                <div class="actions mt-2">
                    <button type="button" onclick="closeAnnouncement()" class="px-3 py-1 bg-gray-300 rounded">Close</button>
                </div>
            @endif
        </div>
    </div>

    <script>
    const userRole = "{{ auth()->user()->role }}";

    function toggleHub() {
        document.getElementById('chatHub').classList.toggle('hidden');
    }

    function openClassChat() {
        document.getElementById('chatBadge').style.display = 'none';
        document.getElementById('classChat').classList.remove('hidden');
    }

    document.getElementById('chatFab').onclick = toggleHub;

    function openAnnouncement() {
        if(userRole === 'teacher' || userRole === 'student'){
            document.getElementById('announcementModal').classList.remove('hidden');
        }
    }

    function closeAnnouncement() {
        document.getElementById('announcementModal').classList.add('hidden');
    }

    function editAnnouncement(button) {
        if(userRole !== 'teacher') return alert("Only teachers can edit announcements.");
        const item = button.closest('.announcement-item');
        alert("Edit announcement: " + item.querySelector('h4').innerText);
    }

    function deleteAnnouncement(button) {
        if(userRole !== 'teacher') return alert("Only teachers can delete announcements.");
        const item = button.closest('.announcement-item');
        item.remove();
    }

    const form = document.getElementById('createAnnouncementForm');
    if(form){
        form.addEventListener('submit', function(e){
            e.preventDefault();
            const title = this.querySelector('input').value;
            const detail = this.querySelector('textarea').value;
            const date = this.querySelector('input[type="date"]').value;

            const list = document.getElementById('announcementList');
            const newItem = document.createElement('div');
            newItem.classList.add('announcement-item', 'border', 'p-3', 'mb-2', 'rounded');
            newItem.innerHTML = `
                <h4>${title}</h4>
                <p>${detail}</p>
                <small>${date}</small>
                <div class="actions mt-2">
                    <button onclick="editAnnouncement(this)" class="px-2 py-1 bg-yellow-400 rounded">Edit</button>
                    <button onclick="deleteAnnouncement(this)" class="px-2 py-1 bg-red-500 text-white rounded">Delete</button>
                </div>
            `;
            list.appendChild(newItem);
            this.reset();
            alert("Announcement created!");
        });
    }
    </script>
</body>
</html>