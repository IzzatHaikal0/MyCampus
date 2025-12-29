<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CommunicationHubController extends Controller
{
    // Show Chat page
    public function chat()
    {
        return view('CommunicationHub.chat');
    }

    // Show Announcement page
    public function announcement()
    {
        return view('CommunicationHub.announcement');
    }
}
