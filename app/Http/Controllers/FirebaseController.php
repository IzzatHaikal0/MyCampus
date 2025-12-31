<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Auth; // <- import Firebase Auth

class FirebaseController extends Controller
{
    protected $auth;

    // Inject Firebase Auth via the service provider
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    // Example method to list users
    public function listUsers()
    {
        $users = $this->auth->listUsers();
        return response()->json($users);
    }
}
