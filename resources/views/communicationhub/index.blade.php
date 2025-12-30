@extends('layouts.app')

@section('content')
<div class="communication-hub">

    {{-- Announcement section --}}
    @include('CommunicationHub.partials.announcement')

    {{-- Chat popup --}}
    @include('CommunicationHub.partials.chat')

</div>
@endsection


@push('scripts')
<script>
document.getElementById('chat-toggle')?.addEventListener('click', () => {
    const win = document.getElementById('chat-window');
    win.style.display = win.style.display === 'none' ? 'block' : 'none';
});

document.getElementById('chat-form')?.addEventListener('submit', function(e){
    e.preventDefault();

    const messageInput = document.getElementById('message');
    const message = messageInput.value.trim();
    if (!message) return;

    fetch("{{ route('chat.send') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ message })
    });

    messageInput.value = '';
});
</script>
@endpush
