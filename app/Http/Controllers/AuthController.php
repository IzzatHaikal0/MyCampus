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
        $credentialsPath = realpath(env('FIREBASE_CREDENTIALS'));

        if (!$credentialsPath || !file_exists($credentialsPath)) {
            throw new \Exception("Firebase credentials not found at: {$credentialsPath}");
        }

        $firebase = (new Factory)
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
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:student,teacher,administrator',
        ]);

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

            // Save user details in Firebase Realtime Database
            $this->database->getReference("users/{$uid}")->set([
                'name' => $request->name,
                'email' => $request->email,
                'role' => $request->role,
            ]);

            return redirect()->route('login')
                ->with('success', 'Account created successfully! You can now login.');

        } catch (\Kreait\Firebase\Exception\Auth\EmailExists $e) {
            return back()->withErrors(['email' => 'Email already exists.']);
        } catch (\Exception $e) {
            return back()->withErrors(['general' => 'Registration failed: ' . $e->getMessage()]);
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
