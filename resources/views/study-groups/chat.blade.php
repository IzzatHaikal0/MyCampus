<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $group['name'] ?? 'Study Group' }} Chat</title>

<script src="https://cdn.tailwindcss.com"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gradient-to-br from-purple-400 via-pink-500 to-red-500 min-h-screen">

<div class="flex">
    @include('layouts.sidebar')

    <main class="flex-1 ml-72 p-6">
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-6 flex justify-between items-center">
            <div class="flex items-center gap-3 text-gray-600">
                <i class="fas fa-comments"></i>
                <span class="font-medium">{{ $group['name'] ?? 'Study Group' }} Chat</span>
            </div>
            <div class="flex items-center gap-3">
                <img src="https://ui-avatars.com/api/?name={{ urlencode(session('firebase_user.name','User')) }}&background=667eea&color=fff"
                     class="w-10 h-10 rounded-full">
                <span class="font-semibold text-gray-800">{{ session('firebase_user.name','User') }}</span>
            </div>
        </div>

        <!-- Chat Window -->
        <div id="chatWindow" class="bg-white p-5 rounded-2xl shadow-md h-[60vh] overflow-y-auto mb-6 space-y-4">
            @foreach($messages as $msg)
                @php $isMe = ($msg['user_id'] ?? '') === session('firebase_user.uid'); @endphp
                <div class="flex {{ $isMe ? 'justify-end' : 'justify-start' }}">
                    <div class="flex items-end gap-2 max-w-xs {{ $isMe ? 'flex-row-reverse' : '' }}">
                        <div class="w-8 h-8 rounded-full bg-purple-400 text-white flex items-center justify-center font-bold text-xs">
                            {{ strtoupper(substr($msg['user_name'] ?? 'U', 0, 2)) }}
                        </div>
                        <div class="px-4 py-2 rounded-2xl break-words
                                    {{ $isMe ? 'bg-purple-500 text-white rounded-br-none' : 'bg-purple-100 text-gray-800 rounded-bl-none' }}">
                            <p class="text-sm font-semibold">{{ $msg['user_name'] ?? 'Unknown' }}</p>
                            <p>{{ $msg['message'] ?? '' }}</p>
                            @if(!empty($msg['file_path']))
                                <a href="{{ asset('storage/' . $msg['file_path']) }}" target="_blank" class="text-blue-500 underline text-sm">Download File</a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Chat Input -->
        <form id="chatForm" enctype="multipart/form-data" class="flex items-center gap-3 bg-white p-4 rounded-2xl shadow-md">
            @csrf
            <input type="text" name="message" placeholder="Type your message..."
                   class="flex-1 p-3 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500">
            <label class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-2 rounded-xl cursor-pointer flex items-center gap-1">
                <i class="fas fa-paperclip"></i> Add File
                <input type="file" name="file" class="hidden" onchange="updateFileName()">
            </label>
            <span id="fileName" class="text-gray-600 text-sm ml-2"></span>
            <button type="submit" class="bg-purple-500 text-white px-4 py-2 rounded-xl hover:bg-purple-600 transition">Send</button>
        </form>
    </main>
</div>

<script>
function updateFileName(){
    const fileInput = document.querySelector('input[name="file"]');
    const fileName = document.getElementById('fileName');
    fileName.textContent = fileInput.files.length > 0 ? fileInput.files[0].name : '';
}

// AJAX submit
$('#chatForm').submit(function(e){
    e.preventDefault();
    let formData = new FormData(this);
    $.ajax({
        url: "/study-groups/{{ $groupId }}/message",
        type: "POST",
        data: formData,
        contentType: false,
        processData: false,
        success: function(msg){
            // Append new message
            let isMe = msg.user_id === "{{ session('firebase_user.uid') }}";
            let html = `<div class="flex ${isMe ? 'justify-end' : 'justify-start'}">
                <div class="flex items-end gap-2 max-w-xs ${isMe ? 'flex-row-reverse' : ''}">
                    <div class="w-8 h-8 rounded-full bg-purple-400 text-white flex items-center justify-center font-bold text-xs">
                        ${msg.user_name.slice(0,2).toUpperCase()}
                    </div>
                    <div class="px-4 py-2 rounded-2xl break-words ${isMe ? 'bg-purple-500 text-white rounded-br-none' : 'bg-purple-100 text-gray-800 rounded-bl-none'}">
                        <p class="text-sm font-semibold">${msg.user_name}</p>
                        <p>${msg.message}</p>
                        ${msg.file_path ? `<a href="/storage/${msg.file_path}" target="_blank" class="text-blue-500 underline text-sm">Download File</a>` : ''}
                    </div>
                </div>
            </div>`;
            $('#chatWindow').append(html);
            $('#chatWindow').scrollTop($('#chatWindow')[0].scrollHeight);
            $('#chatForm')[0].reset();
            $('#fileName').text('');
        },
        error: function(err){
            alert(err.responseJSON.error);
        }
    });
});
</script>

</body>
</html>
