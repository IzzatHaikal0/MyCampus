<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $group->name }} Chat</title>

<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-purple-400 via-pink-500 to-red-500 min-h-screen">

<div class="flex">
    <!-- Sidebar -->
    @include('layouts.sidebar')

    <!-- Main Content -->
    <main class="flex-1 ml-72 p-6">

        <!-- Top Header -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6 flex justify-between items-center">
            <div class="flex items-center gap-3 text-gray-600">
                <i class="fas fa-comments"></i>
                <span class="font-medium"> > {{ $group->name }} Chat</span>
            </div>

            <div class="flex items-center gap-3">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(session('firebase_user.name', 'User')) }}&background=667eea&color=fff" 
                     alt="User" class="w-10 h-10 rounded-full">
                <span class="font-semibold text-gray-800">{{ session('firebase_user.name', 'User') }}</span>
            </div>
        </div>

        <!-- Chat Window -->
        <div id="chatWindow" class="bg-white p-5 rounded-2xl shadow-md h-[60vh] overflow-y-auto mb-6 space-y-4">
            @if($group->messages->count())
                @foreach($group->messages as $message)
                    <div class="flex {{ $message->firebase_uid === session('firebase_user.uid') ? 'justify-end' : 'justify-start' }}">
                        <div class="flex items-end gap-2 max-w-xs {{ $message->firebase_uid === session('firebase_user.uid') ? 'flex-row-reverse' : '' }}">
                            <div class="w-8 h-8 rounded-full bg-purple-400 text-white flex items-center justify-center font-bold">
                                {{ strtoupper(substr($message->sender_name ?? 'U', 0, 2)) }}
                            </div>
                            <div class="px-4 py-2 rounded-2xl break-words
                                {{ $message->firebase_uid === session('firebase_user.uid') ? 'bg-purple-500 text-white rounded-br-none' : 'bg-purple-100 text-gray-800 rounded-bl-none' }}">
                                <p class="text-sm font-semibold">{{ $message->sender_name ?? 'Unknown' }}</p>
                                <p>{{ $message->message }}</p>
                                @if($message->file_path)
                                    <a href="{{ asset('storage/' . $message->file_path) }}" target="_blank" class="text-blue-500 underline text-sm">Download File</a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <p class="text-gray-500 text-center mt-10">No messages yet. Start the conversation!</p>
            @endif
        </div>

        <!-- Chat Input -->
<form action="{{ route('study-groups.sendMessage', $group) }}" 
      method="POST" 
      enctype="multipart/form-data" 
      class="flex items-center gap-3 bg-white p-4 rounded-2xl shadow-md">
    @csrf
    <input type="text" name="message" placeholder="Type your message..." class="flex-1 p-3 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500">

    <label for="chatFile" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-2 rounded-xl cursor-pointer flex items-center gap-1">
        <i class="fas fa-paperclip"></i> Add File
    </label>
    <input type="file" id="chatFile" name="file" class="hidden" onchange="updateFileName()">
    <span id="fileName" class="text-gray-600 text-sm ml-2"></span>

    <button type="submit" class="bg-purple-500 text-white px-4 py-2 rounded-xl hover:bg-purple-600 transition">
        Send
    </button>
</form>



    </main>
</div>

<script>
    // Update nama file bila pilih
    function updateFileName() {
        const fileInput = document.getElementById('chatFile');
        const fileName = document.getElementById('fileName');
        if(fileInput.files.length > 0){
            fileName.textContent = fileInput.files[0].name;
        } else {
            fileName.textContent = '';
        }
    }

    // Auto scroll ke bawah chat window bila page load
    const chatWindow = document.getElementById('chatWindow');
    chatWindow.scrollTop = chatWindow.scrollHeight;
</script>

</body>
</html>
