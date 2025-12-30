<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;

class CommunicationHubController extends Controller
{
    // Main Communication Hub page
    public function index()
    {
        $messages = Message::with('user')
            ->orderBy('created_at')
            ->get();

        return view('CommunicationHub.index', compact('messages'));
    }
}
