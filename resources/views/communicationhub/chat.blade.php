@extends('layouts.app')

@section('content')
<div class="chat-container" style="max-width:600px;margin:50px auto;border:1px solid #ccc;border-radius:8px;padding:20px;">
    <!-- Messages Display -->
    <div id="messages" style="height:400px;overflow-y:auto;padding:10px;border:1px solid #ddd;border-radius:5px;margin-bottom:10px;background:#f9f9f9;">
        @foreach($messages as $message)
            <div class="message {{ $message->user_id === auth()->id() ? 'sent' : 'received' }}" 
                 style="margin-bottom:8px; max-width:80%; padding:8px 12px; border-radius:15px; 
                        background: {{ $message->user_id === auth()->id() ? '#007bff' : '#e5e5ea' }}; 
                        color: {{ $message->user_id === auth()->id() ? '#fff' : '#000' }};
                        margin-left: {{ $message->user_id === auth()->id() ? 'auto' : '0' }};
                        margin-right: {{ $message->user_id === auth()->id() ? '0' : 'auto' }};">
                <div><strong>{{ $message->user->name }}:</strong> {{ $message->content }}</div>
                <div style="font-size:10px; color: {{ $message->user_id === auth()->id() ? '#e0e0e0' : '#555' }}; text-align: right; margin-top:3px;">
                    {{ $message->created_at->format('H:i') }}
                </div>
            </div>
        @endforeach
    </div>

    <!-- Chat Form -->
    <form id="chat-form" action="{{ route('chat.send') }}" method="POST" style="display:flex;">
        @csrf
        <input type="text" name="message" id="message" placeholder="Type your message..." required
               style="flex:1;padding:8px;border-radius:20px;border:1px solid #ccc;margin-right:5px;">
        <button type="submit" style="padding:8px 15px;border:none;background:#007bff;color:#fff;border-radius:20px;">Send</button>
    </form>
</div>
@endsection

@section('scripts')
<script src="https://js.pusher.com/7.2/pusher.min.js"></script>
<script src="{{ asset('js/app.js') }}"></script> <!-- Laravel Echo -->

<script>
document.getElementById('chat-form').addEventListener('submit', function(e){
    e.preventDefault();

    let message = document.getElementById('message').value;
    if(message.trim() === '') return;

    fetch("{{ route('chat.send') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ message: message })
    })
    .then(res => res.json())
    .then(data => {
        const msgDiv = document.createElement('div');
        msgDiv.className = 'message sent';
        msgDiv.style.cssText = "margin-bottom:8px; max-width:80%; padding:8px 12px; border-radius:15px; background:#007bff; color:#fff; margin-left:auto; margin-right:0;";

        const time = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        msgDiv.innerHTML = `<div><strong>{{ auth()->user()->name }}:</strong> ${data.message}</div>
                            <div style="font-size:10px; color:#e0e0e0; text-align:right; margin-top:3px;">${time}</div>`;

        document.getElementById('messages').appendChild(msgDiv);
        document.getElementById('message').value = '';
        document.getElementById('messages').scrollTop = document.getElementById('messages').scrollHeight;
    });
});

// Real-time listening for other users
window.Echo.channel('chat')
    .listen('MessageSent', (e) => {
        if(e.message.user.id === {{ auth()->id() }}) return;

        const msgDiv = document.createElement('div');
        msgDiv.className = 'message received';
        msgDiv.style.cssText = "margin-bottom:8px; max-width:80%; padding:8px 12px; border-radius:15px; background:#e5e5ea; color:#000; margin-left:0; margin-right:auto;";

        const time = new Date(e.message.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        msgDiv.innerHTML = `<div><strong>${e.message.user.name}:</strong> ${e.message.content}</div>
                            <div style="font-size:10px; color:#555; text-align:right; margin-top:3px;">${time}</div>`;

        document.getElementById('messages').appendChild(msgDiv);
        document.getElementById('messages').scrollTop = document.getElementById('messages').scrollHeight;
    });

// Scroll to bottom on page load
document.getElementById('messages').scrollTop = document.getElementById('messages').scrollHeight;
</script>
@endsection
