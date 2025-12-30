<div class="chat-popup">

    <!-- Toggle Button -->
    <button id="chat-toggle"
            style="position:fixed;bottom:30px;right:30px;
                   background:#007bff;color:#fff;
                   border:none;border-radius:50%;
                   width:60px;height:60px;font-size:24px;">
        💬
    </button>

    <!-- Chat Window -->
    <div id="chat-window"
         style="display:none;position:fixed;bottom:100px;right:30px;
                width:360px;background:#fff;border:1px solid #ccc;
                border-radius:10px;box-shadow:0 5px 20px rgba(0,0,0,.2);">

        <div class="chat-container" style="padding:15px;">

            <!-- Messages -->
            <div id="messages"
                 style="height:300px;overflow-y:auto;padding:10px;
                        border:1px solid #ddd;border-radius:5px;
                        background:#f9f9f9;margin-bottom:10px;">

                @foreach($messages as $message)
                    <div style="margin-bottom:8px;
                                max-width:80%;
                                padding:8px 12px;
                                border-radius:15px;
                                background: {{ $message->user_id === auth()->id() ? '#007bff' : '#e5e5ea' }};
                                color: {{ $message->user_id === auth()->id() ? '#fff' : '#000' }};
                                margin-left: {{ $message->user_id === auth()->id() ? 'auto' : '0' }};
                                margin-right: {{ $message->user_id === auth()->id() ? '0' : 'auto' }};">

                        <div>
                            <strong>{{ $message->user->name }}:</strong>
                            {{ $message->content }}
                        </div>

                        <div style="font-size:10px;text-align:right;margin-top:3px;
                                    color: {{ $message->user_id === auth()->id() ? '#e0e0e0' : '#555' }};">
                            {{ $message->created_at->format('H:i') }}
                        </div>
                    </div>
                @endforeach

            </div>

            <!-- Input -->
            <form id="chat-form">
                @csrf
                <input type="text" id="message" placeholder="Type your message..."
                       style="width:100%;padding:8px;border-radius:20px;border:1px solid #ccc;">
            </form>

        </div>
    </div>
</div>
