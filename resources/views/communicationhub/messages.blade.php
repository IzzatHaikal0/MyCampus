<div id="chat-messages" class="flex flex-col space-y-2 p-2 overflow-y-auto" style="max-height: 400px;">
    @foreach($messages ?? [] as $message)
        <div class="message {{ $message->user_id === auth()->id() ? 'sent' : 'received' }}"
             style="max-width:80%; padding:8px 12px; border-radius:15px; 
                    background: {{ $message->user_id === auth()->id() ? '#007bff' : '#e5e5ea' }};
                    color: {{ $message->user_id === auth()->id() ? '#fff' : '#000' }};
                    align-self: {{ $message->user_id === auth()->id() ? 'flex-end' : 'flex-start' }};">
            <div><strong>{{ $message->user->name }}:</strong> {{ $message->content }}</div>
            <div style="font-size:10px; color: {{ $message->user_id === auth()->id() ? '#e0e0e0' : '#555' }}; text-align: right; margin-top:3px;">
                {{ $message->created_at->format('H:i') }}
            </div>
        </div>
    @endforeach
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatContainer = document.getElementById('chat-messages');
    if(chatContainer) {
        // Scroll to bottom on load
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    // Auto-scroll when new messages are added
    const observer = new MutationObserver(() => {
        chatContainer.scrollTop = chatContainer.scrollHeight;
    });

    observer.observe(chatContainer, { childList: true });
});
</script>
@endpush
