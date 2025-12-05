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
        $credentialsPath = env('FIREBASE_CREDENTIALS');

        if (empty($credentialsPath)) {
            throw new \Exception("FIREBASE_CREDENTIALS path not defined in .env");
        }

        $credentialsPath = realpath($credentialsPath);

        if (!$credentialsPath || !file_exists($credentialsPath)) {
            throw new \Exception("Firebase credentials not found at: {$credentialsPath}");
        }

        $firebase = (new Factory)
            ->withServiceAccount($credentialsPath)
            ->withDatabaseUri(env('FIREBASE_DATABASE_URL'));

        $this->auth = $firebase->createAuth();
        $this->database = $firebase->createDatabase();
    }

    public function showRegisterForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:student,teacher,administrator',
        ]);

        try {
            $user = $this->auth->createUser([
                'email' => $request->email,
                'emailVerified' => false,
                'password' => $request->password,
                'displayName' => $request->name,
                'disabled' => false,
            ]);

            $uid = $user->uid;

            $this->database->getReference('users/'.$uid)->set([
                'name' => $request->name,
                'email' => $request->email,
                'role' => $request->role,
            ]);

            return redirect()->route('login')
                ->with('success', 'Account created successfully! You can now login.');

        } catch (\Kreait\Firebase\Exception\Auth\EmailExists $e) {
            return back()->withErrors(['email' => 'Email already exists.']);
        } catch (\Exception $e) {
            return back()->withErrors(['general' => 'Registration failed: '.$e->getMessage()]);
        }
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

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

            Session::put('firebase_user', [
                'uid' => $uid,
                'email' => $firebaseUser['email'],
                'name' => $firebaseUser['displayName'] ?? '',
                'role' => $role,
            ]);

            if ($role === 'administrator') {
                return redirect()->route('admin.dashboard')->with('success', 'Welcome Administrator!');
            } elseif ($role === 'teacher') {
                return redirect()->route('teacher.dashboard')->with('success', 'Welcome Teacher!');
            } else {
                return redirect()->route('student.dashboard')->with('success', 'Welcome Student!');
            }

        } catch (InvalidPassword $e) {
            return back()->withErrors(['password' => 'Invalid password.']);
        } catch (UserNotFound $e) {
            return back()->withErrors(['email' => 'User not found.']);
        } catch (\Exception $e) {
            return back()->withErrors(['general' => 'Login failed: '.$e->getMessage()]);
        }
    }

    public function logout()
    {
        Session::forget('firebase_user');
        return redirect()->route('login')->with('success', 'Successfully logged out.');
    }
}
