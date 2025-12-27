<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\StudyGroup;
use App\Models\ChatMessage;

class StudyGroupController extends Controller
{
    /* ===============================
       LIST GROUP (OWNER + JOINED)
    =============================== */
    public function index()
    {
        $userId = session('firebase_user.uid');

        $groups = StudyGroup::where('owner_uid', $userId)
            ->orWhereIn('id', function ($q) use ($userId) {
                $q->select('group_id')
                  ->from('study_group_user')
                  ->where('user_id', $userId);
            })
            ->get();

        return view('study-groups.index', compact('groups'));
    }

    /* ===============================
       CREATE GROUP FORM
    =============================== */
    public function create()
    {
        return view('study-groups.create');
    }

    /* ===============================
       STORE GROUP
    =============================== */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        StudyGroup::create([
            'name'        => $request->name,
            'subject'     => $request->subject,
            'description' => $request->description,
            'owner_uid'   => session('firebase_user.uid'),
            'owner_name'  => session('firebase_user.name'),
            'join_code'   => strtoupper(substr(md5(time()), 0, 6)),
        ]);

        return redirect()->route('study-groups.index')->with('success', 'Group created successfully!');
    }

    /* ===============================
       JOIN GROUP USING CODE
    =============================== */
    public function joinByCode(Request $request)
    {
        $request->validate([
            'code' => 'required'
        ]);

        $group = StudyGroup::where('join_code', $request->code)->first();

        if (!$group) {
            return back()->with('error', 'Invalid join code');
        }

        DB::table('study_group_user')->updateOrInsert(
            [
                'group_id' => $group->id,
                'user_id'  => session('firebase_user.uid'),
            ],
            [
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return redirect()->route('study-groups.chat', $group->id);
    }

    /* ===============================
       GROUP CHAT PAGE
    =============================== */
   public function chat(StudyGroup $study_group)
{
    $study_group->load('messages');

    $group = $study_group;

    return view('study-groups.chat', compact('group'));
}


    /* ===============================
       SEND MESSAGE (TEXT + FILE)
    =============================== */
    public function sendMessage(Request $request, StudyGroup $study_group)
    {
        $request->validate([
            'message' => 'nullable|string',
            'file' => 'nullable|file|max:10240', // max 10MB
        ]);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('chat_files', 'public');
        }

        ChatMessage::create([
            'study_group_id' => $study_group->id,
            'firebase_uid' => session('firebase_user.uid'),
            'sender_name' => session('firebase_user.name'),
            'message' => $request->message,
            'file_path' => $filePath,
        ]);

        return back();
    }

    /* ===============================
       DELETE GROUP
    =============================== */
    public function destroy(StudyGroup $study_group)
{
    // Delete semua chat messages
    ChatMessage::where('study_group_id', $study_group->id)->delete();

    // Delete semua pivot entries
    DB::table('study_group_user')->where('group_id', $study_group->id)->delete();

    // Delete group sendiri
    $study_group->delete();

    return redirect()->route('study-groups.index')->with('success', 'Group deleted successfully!');
}


    /* ===============================
       EDIT GROUP
    =============================== */
    public function edit(StudyGroup $study_group)
    {
        return view('study-groups.edit', ['study_group' => $study_group]);
    }

    /* ===============================
       UPDATE GROUP
    =============================== */
    public function update(Request $request, StudyGroup $study_group)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $study_group->update([
            'name' => $request->name,
            'subject' => $request->subject,
            'description' => $request->description,
        ]);

        return redirect()->route('study-groups.index')->with('success', 'Group updated successfully!');
    }
}
