<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use App\Events\MessageSent;
use App\Services\FirebaseService;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    // Handle sending a new message
    public function send(Request $request, FirebaseService $firebase)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'message' => 'required|string',
        ]);

        // Save message to local database
        $message = Message::create([
            'user_id' => Auth::id(),
            'content' => $request->message,
        ]);

        // Broadcast message to others (existing Pusher/Laravel Echo)
        broadcast(new MessageSent($message))->toOthers();

        // Send message to Firebase for real-time sync
        $firebase->sendMessage([
            'user_id' => Auth::id(),
            'user_name' => Auth::user()->name,
            'content' => $message->content,
            'created_at' => now()->toDateTimeString(),
        ]);

        // Return response to AJAX
        return response()->json([
            'message' => $message->content,
            'user' => Auth::user()->name
        ]);
    }
}
