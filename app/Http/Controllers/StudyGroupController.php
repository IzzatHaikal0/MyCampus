<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;

class StudyGroupController extends Controller
{
    protected $db;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(env('FIREBASE_CREDENTIALS'))
            ->withDatabaseUri(env('FIREBASE_DATABASE_URL'));

        $this->db = $factory->createDatabase();
    }

    /* ===============================
       LIST GROUP (OWNER + JOINED)
    =============================== */
    public function index()
    {
        $userId = session('firebase_user.uid');

        $allGroups = $this->db->getReference('study-groups')->getValue() ?? [];

        $groups = [];
        foreach ($allGroups as $id => $group) {
            if (($group['owner_uid'] ?? null) === $userId ||
                isset($group['members']) && array_key_exists($userId, $group['members'])) {
                $groups[$id] = $group;
            }
        }

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

        $groupData = [
            'name' => $request->name,
            'subject' => $request->subject,
            'description' => $request->description,
            'owner_uid' => session('firebase_user.uid'),
            'owner_name' => session('firebase_user.name'),
            'join_code' => strtoupper(substr(md5(time()), 0, 6)),
            'created_at' => now()->toDateTimeString(),
            'members' => []
        ];

        $this->db->getReference('study-groups')->push($groupData);

        return redirect()->route('study-groups.index')->with('success', 'Group created successfully!');
    }

    /* ===============================
       JOIN GROUP USING CODE
    =============================== */
    public function joinByCode(Request $request)
    {
        $request->validate(['code' => 'required']);

        $allGroups = $this->db->getReference('study-groups')->getValue() ?? [];

        $foundId = null;
        foreach ($allGroups as $id => $group) {
            if (($group['join_code'] ?? null) === $request->code) {
                $foundId = $id;
                break;
            }
        }

        if (!$foundId) {
            return back()->with('error', 'Invalid join code');
        }

        $userId = session('firebase_user.uid');
        $this->db->getReference("study-groups/{$foundId}/members/{$userId}")
            ->set([
                'name' => session('firebase_user.name'),
                'joined_at' => now()->toDateTimeString()
            ]);

        return redirect()->route('study-groups.chat', $foundId);
    }

    /* ===============================
       GROUP CHAT PAGE
    =============================== */
    public function chat($groupId)
    {
        $group = $this->db->getReference("study-groups/{$groupId}")->getValue();
        $messages = $this->db->getReference("study-groups/{$groupId}/messages")->getValue() ?? [];
        
        if($messages){
            $messages = collect($messages)->sortBy('created_at')->toArray();
        }

        return view('study-groups.chat', compact('group', 'messages', 'groupId'));
    }
    /* ===============================
       SEND MESSAGE
    =============================== */
     public function sendMessage(Request $request, $groupId)
    {
        // Kalau tak ada mesej dan tak ada file, return error
        if(!$request->filled('message') && !$request->hasFile('file')){
            return response()->json(['error' => 'Please provide a message or file.'], 422);
        }

        $filePath = null;
        if($request->hasFile('file')){
            $filePath = $request->file('file')->store('chat_files', 'public');
        }

        $newMessage = [
            'user_id' => session('firebase_user.uid'),
            'user_name' => session('firebase_user.name'),
            'message' => $request->message ?? '',
            'file_path' => $filePath,
            'created_at' => now()->toDateTimeString(),
        ];

        $this->db->getReference("study-groups/{$groupId}/messages")->push($newMessage);

        return response()->json($newMessage);
    }


    /* ===============================
       DELETE GROUP
    =============================== */
    public function destroy($groupId)
    {
        $this->db->getReference("study-groups/{$groupId}")->remove();
        return redirect()->route('study-groups.index')->with('success', 'Group deleted successfully!');
    }

    /* ===============================
       EDIT GROUP
    =============================== */
    public function edit($groupId)
    {
        $group = $this->db->getReference("study-groups/{$groupId}")->getValue();
        return view('study-groups.edit', compact('group', 'groupId'));
    }

    /* ===============================
       UPDATE GROUP
    =============================== */
    public function update(Request $request, $groupId)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $this->db->getReference("study-groups/{$groupId}")->update([
            'name' => $request->name,
            'subject' => $request->subject,
            'description' => $request->description,
        ]);

        return redirect()->route('study-groups.index')->with('success', 'Group updated successfully!');
    }
}
