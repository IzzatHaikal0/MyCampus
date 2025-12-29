<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use App\Events\MessageSent;
use Illuminate\Support\Facades\Auth;


class ChatController extends Controller
{
    public function index()
    {
        $messages = Message::with('user')->orderBy('created_at')->get();
        return view('chat', compact('messages'));
    }

    public function send(Request $request)
{
    if (!Auth::check()) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $request->validate([
        'message' => 'required|string',
    ]);

    $message = Message::create([
        'user_id' => Auth::id(),
        'content' => $request->message,
    ]);

    broadcast(new MessageSent($message))->toOthers();

    return response()->json([
        'message' => $message->content,
        'user' => Auth::user()->name
    ]);
}

}
