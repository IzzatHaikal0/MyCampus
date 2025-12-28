<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Kreait\Firebase\Factory;
use Illuminate\Support\Facades\Session;
use Kreait\Firebase\Exception\Auth\InvalidPassword;
use Kreait\Firebase\Exception\Auth\UserNotFound;

class AuthController extends Controller
{
    protected $auth;
    protected $database;

    public function __construct()
{
    // Get Firebase credentials path from .env
    $credentialsPath = env('FIREBASE_CREDENTIALS');

    // Convert backslashes to forward slashes for Windows compatibility
    $credentialsPath = str_replace('\\', '/', $credentialsPath);

    // Resolve absolute path
    $credentialsPath = realpath($credentialsPath);

    // Check if the file exists
    if (!$credentialsPath || !file_exists($credentialsPath)) {
        throw new \Exception("Firebase credentials not found at: {$credentialsPath}");
    }

    // Initialize Firebase
    $firebase = (new \Kreait\Firebase\Factory)
        ->withServiceAccount($credentialsPath)
        ->withDatabaseUri(env('FIREBASE_DATABASE_URL'));

    $this->auth = $firebase->createAuth();
    $this->database = $firebase->createDatabase();
}


    /**
     * Show the registration form.
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * Handle registration.
     */
    public function register(Request $request)
    {
        // Base validation rules
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:student,teacher,administrator',
        ];

        // Add class_section validation only for students
        if ($request->role === 'student') {
            $rules['class_section'] = 'required|string';
        }

        $request->validate($rules);

        try {
            // Create Firebase user
            $user = $this->auth->createUser([
                'email' => $request->email,
                'emailVerified' => false,
                'password' => $request->password,
                'displayName' => $request->name,
                'disabled' => false,
            ]);

            $uid = $user->uid;

            // Prepare user data
            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'role' => $request->role,
            ];

            // Add class_section only if user is a student
            if ($request->role === 'student') {
                $userData['class_section'] = $request->class_section;
            }

            // Save user details in Firebase Realtime Database
            $this->database->getReference("users/{$uid}")->set($userData);

            return redirect()->route('login')
                ->with('success', 'Account created successfully! You can now login.');

        } catch (\Kreait\Firebase\Exception\Auth\EmailExists $e) {
            return back()->withErrors(['email' => 'Email already exists.']);
        } catch (\Exception $e) {
            return back()->withErrors(['general' => 'Registration failed: ' . $e->getMessage()]);
        }
    }

    public function profile()
    {
        // Get the current user from session
        $sessionUser = Session::get('firebase_user');
        
        if (!$sessionUser) {
            return redirect()->route('login')->withErrors(['general' => 'Please login first.']);
        }
        
        $uid = $sessionUser['uid'];
        
        try {
            // Get user data from Firebase
            $userData = $this->database->getReference("users/{$uid}")->getValue();
            
            // Get Firebase Auth user data
            $firebaseUser = $this->auth->getUser($uid);
            
            // Merge data
            $user = [
                'uid' => $uid,
                'name' => $userData['name'] ?? $firebaseUser->displayName ?? '',
                'email' => $userData['email'] ?? $firebaseUser->email,
                'role' => $userData['role'] ?? 'student',
                'class_section' => $userData['class_section'] ?? null,
                'email_verified' => $firebaseUser->emailVerified,
            ];
            
            return view('profile.profile', compact('user'));
            
        } catch (\Exception $e) {
            return back()->withErrors(['general' => 'Failed to load profile: ' . $e->getMessage()]);
        }
    }
    /**
     * Update user profile.
    */
    public function updateProfile(Request $request)
    {
        // Get the current user from session
        $sessionUser = Session::get('firebase_user');
        
        if (!$sessionUser) {
            return redirect()->route('login')->withErrors(['general' => 'Please login first.']);
        }
        
        $uid = $sessionUser['uid'];
        
        // Base validation rules
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email',
        ];
        
        // Add class_section validation only for students
        if ($sessionUser['role'] === 'student') {
            $rules['class_section'] = 'required|string';
        }
        
        $request->validate($rules);
        
        try {
            // Update Firebase Auth user
            $properties = [
                'displayName' => $request->name,
                'email' => $request->email,
            ];
            
            $this->auth->updateUser($uid, $properties);
            
            // Prepare database update
            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
            ];
            
            // Add class_section only if user is a student
            if ($sessionUser['role'] === 'student') {
                $updateData['class_section'] = $request->class_section;
            }
            
            // Update Firebase Realtime Database
            $this->database->getReference("users/{$uid}")->update($updateData);
            
            // Update session data
            Session::put('firebase_user', [
                'uid' => $uid,
                'email' => $request->email,
                'name' => $request->name,
                'role' => $sessionUser['role'],
            ]);
            
            return back()->with('success', 'Profile updated successfully!');
            
        } catch (\Kreait\Firebase\Exception\Auth\EmailExists $e) {
            return back()->withErrors(['email' => 'This email is already in use by another account.']);
        } catch (\Exception $e) {
            return back()->withErrors(['general' => 'Failed to update profile: ' . $e->getMessage()]);
        }
    }

    /**
     * Update user password.
     */
    public function updatePassword(Request $request)
    {
        // Get the current user from session
        $sessionUser = Session::get('firebase_user');
        
        if (!$sessionUser) {
            return redirect()->route('login')->withErrors(['general' => 'Please login first.']);
        }
        
        $uid = $sessionUser['uid'];
        
        $request->validate([
            'current_password' => 'required|string|min:6',
            'password' => 'required|string|min:6|confirmed',
        ]);
        
        try {
            // Verify current password by attempting to sign in
            try {
                $this->auth->signInWithEmailAndPassword(
                    $sessionUser['email'],
                    $request->current_password
                );
            } catch (InvalidPassword $e) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }
            
            // Update password in Firebase Auth
            $this->auth->updateUser($uid, [
                'password' => $request->password,
            ]);
            
            return back()->with('success', 'Password updated successfully!');
            
        } catch (\Exception $e) {
            return back()->withErrors(['general' => 'Failed to update password: ' . $e->getMessage()]);
        }
    }
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login and redirect based on role.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        try {
            $signInResult = $this->auth->signInWithEmailAndPassword(
                $request->email,
                $request->password
            );

            $firebaseUser = $signInResult->data();
            $uid = $firebaseUser['localId'];

            $role = $this->database->getReference("users/{$uid}/role")->getValue() ?? 'student';

            // Save user session
            Session::put('firebase_user', [
                'uid' => $uid,
                'email' => $firebaseUser['email'],
                'name' => $firebaseUser['displayName'] ?? '',
                'role' => $role,
            ]);

            // Redirect to dashboard based on role
            switch ($role) {
                case 'administrator':
                    return redirect()->route('admin.dashboard')->with('success', 'Welcome Administrator!');
                case 'teacher':
                    return redirect()->route('teacher.dashboard')->with('success', 'Welcome Teacher!');
                default:
                    return redirect()->route('student.dashboard')->with('success', 'Welcome Student!');
            }

        } catch (InvalidPassword $e) {
            return back()->withErrors(['password' => 'Invalid password.']);
        } catch (UserNotFound $e) {
            return back()->withErrors(['email' => 'User not found.']);
        } catch (\Exception $e) {
            return back()->withErrors(['general' => 'Login failed: ' . $e->getMessage()]);
        }
    }

    /**
     * Logout user and clear session.
     */
    public function logout()
    {
        Session::forget('firebase_user');
        return redirect()->route('login')->with('success', 'Successfully logged out.');
    }
}
