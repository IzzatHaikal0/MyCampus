<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudyGroup;
use App\Models\ChatMessage;

class ChatController extends Controller
{
    // Papar page chat
    public function show($groupId)
    {
        $group = StudyGroup::with('users', 'messages')->findOrFail($groupId);

        $messages = $group->messages()->orderBy('created_at', 'asc')->get();

        $uid = session('firebase_user.uid');
        $name = session('firebase_user.name');

        return view('study-groups.chat', compact('group', 'messages', 'uid', 'name'));
    }

    // Send message
    public function sendMessage(Request $request, $groupId)
    {
        $request->validate([
            'message' => 'nullable|string',
            'file' => 'nullable|file|max:10240', // max 10MB
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('chat_files', 'public');
        }

        $msg = ChatMessage::create([
            'study_group_id' => $groupId,
            'firebase_uid' => session('firebase_user.uid'),
            'sender_name' => session('firebase_user.name'),
            'message' => $request->message,
            'file_path' => $filePath,
        ]);

        return back(); // reload page
    }

    // API untuk fetch message (optional, kalau nak live update)
    public function fetchMessages($groupId)
    {
        $group = StudyGroup::with('messages')->findOrFail($groupId);
        return response()->json($group->messages()->orderBy('created_at', 'asc')->get());
    }
}
