<!-- Floating Communication Hub -->
<div id="CommunicationHub" class="fixed bottom-6 right-6 w-80 lg:w-96 bg-white dark:bg-[#1a1a1a] rounded-xl shadow-lg overflow-hidden z-50 flex flex-col" style="display:none;">
    <!-- Hub Header -->
    <div class="flex justify-between items-center p-4 border-b border-gray-200 dark:border-gray-700">
        <h3 class="font-semibold text-gray-800 dark:text-gray-100">Chat</h3>
        <button id="hubClose" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">&times;</button>
    </div>

    <!-- Hub Tabs -->
    <div class="flex border-b border-gray-200 dark:border-gray-700">
        <button class="hub-tab flex-1 py-2 text-center text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800" data-tab="messages">Messages</button>
        <button class="hub-tab flex-1 py-2 text-center text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800" data-tab="notifications">Notifications</button>
        <button class="hub-tab flex-1 py-2 text-center text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800" data-tab="announcements">Announcements</button>
    </div>

    <!-- Hub Content -->
    <div class="hub-content flex-1 overflow-y-auto p-2">
        <div id="messages" style="display:none;">@include('CommunicationHub.messages')</div>
        <div id="notifications" style="display:none;">@include('CommunicationHub.notifications')</div>
        <div id="announcements" style="display:none;">@include('CommunicationHub.announcements')</div>
    </div>
</div>

<!-- Floating Hub Button -->
<button id="openHub" class="fixed bottom-6 right-6 z-50 p-2 bg-white rounded-full shadow-lg hover:scale-110 transition transform">
    <img src="{{ asset('images/chat-icon.png') }}" alt="Chat" class="h-10 w-10">
</button>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const hub = document.getElementById('CommunicationHub');
    const openBtn = document.getElementById('openHub');
    const closeBtn = document.getElementById('hubClose');
    const tabs = document.querySelectorAll('.hub-tab');
    const contents = ['messages', 'notifications', 'announcements'];
    const chatContainer = document.getElementById('chat-messages');

    // ----------------------------
    // Open / Close hub
    // ----------------------------
    openBtn.addEventListener('click', () => hub.style.display = 'flex');
    closeBtn.addEventListener('click', () => hub.style.display = 'none');

    // Show first tab by default
    contents.forEach((id, index) => {
        const el = document.getElementById(id);
        if(el) el.style.display = index === 0 ? 'block' : 'none';
    });

    // Tab switching
    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            contents.forEach(c => document.getElementById(c).style.display = 'none');
            document.getElementById(this.dataset.tab).style.display = 'block';
        });
    });

    // ----------------------------
    // Chat form
    // ----------------------------
    const chatForm = document.createElement('form');
    chatForm.id = 'chat-form';
    chatForm.style.display = 'flex';
    chatForm.innerHTML = `
        <input type="text" id="chat-input" placeholder="Type your message..." required
               style="flex:1; padding:8px; border-radius:20px; border:1px solid #ccc; margin-right:5px;">
        <button type="submit" style="padding:8px 15px; border:none; background:#007bff; color:#fff; border-radius:20px;">Send</button>
    `;
    hub.appendChild(chatForm);

    const chatInput = document.getElementById('chat-input');

    chatForm.addEventListener('submit', function(e){
        e.preventDefault();
        let message = chatInput.value.trim();
        if(!message) return;

        // Send to Laravel backend
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
            appendMessage(data.user, data.message, true);
            chatInput.value = '';
        })
        .catch(err => console.error(err));
    });

    // ----------------------------
    // Helper: append a message
    // ----------------------------
    function appendMessage(user, content, isSender = false) {
        if(!chatContainer) return;

        const msgDiv = document.createElement('div');
        msgDiv.className = 'message';
        msgDiv.style.cssText = `
            max-width:80%; padding:8px 12px; border-radius:15px;
            background: ${isSender ? '#007bff' : '#e5e5ea'};
            color: ${isSender ? '#fff' : '#000'};
            align-self: ${isSender ? 'flex-end' : 'flex-start'};
            margin-bottom:8px;
        `;

        const time = new Date().toLocaleTimeString([], {hour:'2-digit', minute:'2-digit'});
        msgDiv.innerHTML = `
            <div><strong>${user}:</strong> ${content}</div>
            <div style="font-size:10px; color:${isSender ? '#e0e0e0' : '#555'}; text-align:right; margin-top:3px;">${time}</div>
        `;

        chatContainer.appendChild(msgDiv);
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    // ----------------------------
    // Real-time listener
    // ----------------------------
    @if(config('broadcasting.default') === 'pusher')
    window.Echo.channel('chat')
        .listen('MessageSent', (e) => {
            if(e.message.user.id === {{ auth()->id() }}) return;
            appendMessage(e.message.user.name, e.message.content, false);
        });
    @endif

    @if(config('firebase.enabled', false))
    const messagesRef = firebase.database().ref('messages');
    messagesRef.on('child_added', snapshot => {
        const data = snapshot.val();
        if(data.user_id === {{ auth()->id() }}) return;
        appendMessage(data.user_name, data.content, false);
    });
    @endif
});
</script>
@endpush

