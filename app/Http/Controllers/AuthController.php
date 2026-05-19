<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\AuditLog;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'su-fname' => 'required|string|max:255',
            'su-lname' => 'required|string|max:255',
            'su-email' => 'required|string|email|max:255|unique:users,email',
            'su-bdate' => 'required|string',
            'su-afftype' => 'required|string',
            'su-affname' => 'required|string|max:255',
            'su-phone' => 'required|string|max:255',
            'su-password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->input('su-fname') . ' ' . $request->input('su-lname'),
            'first_name' => $request->input('su-fname'),
            'last_name' => $request->input('su-lname'),
            'email' => strtolower($request->input('su-email')),
            'password' => Hash::make($request->input('su-password')),
            'phone' => $request->input('su-phone'),
            'birthdate' => $request->input('su-bdate'),
            'affiliation_type' => $request->input('su-afftype'),
            'affiliation_name' => $request->input('su-affname'),
            'is_admin' => false,
            'is_active' => true,
        ]);

        Auth::login($user);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'Registration',
            'description' => $user->name . ' registered an account from ' . $user->affiliation_name . '.',
            'ip_address' => $request->ip()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Account created successfully!',
            'user' => [
                'name' => $user->name,
                'firstName' => $user->first_name,
                'lastName' => $user->last_name,
                'email' => $user->email,
                'bdate' => $user->birthdate,
                'affType' => $user->affiliation_type,
                'affName' => $user->affiliation_name,
                'phone' => $user->phone
            ]
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $email = strtolower($request->input('email'));
        $password = $request->input('password');

        $user = User::where('email', $email)->first();

        // If user doesn't exist, create auto-guest registration as per original frontend script logic!
        if (!$user) {
            $firstName = explode('@', $email)[0];
            $user = User::create([
                'name' => $firstName,
                'first_name' => $firstName,
                'last_name' => '',
                'email' => $email,
                'password' => Hash::make($password),
                'is_admin' => false,
                'is_active' => true,
            ]);
            
            Auth::login($user);

            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'Registration',
                'description' => 'Guest account auto-created: ' . $email,
                'ip_address' => $request->ip()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Guest account created successfully!',
                'user' => [
                    'name' => $user->name,
                    'firstName' => $user->first_name,
                    'lastName' => $user->last_name,
                    'email' => $user->email,
                ]
            ]);
        }

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been deactivated. Please contact support.'
            ], 403);
        }

        if (!Hash::check($password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Incorrect password.'
            ], 401);
        }

        Auth::login($user);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'Login',
            'description' => $user->name . ' logged into the application.',
            'ip_address' => $request->ip()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Welcome back!',
            'user' => [
                'name' => $user->name,
                'firstName' => $user->first_name,
                'lastName' => $user->last_name,
                'email' => $user->email,
                'bdate' => $user->birthdate,
                'affType' => $user->affiliation_type,
                'affName' => $user->affiliation_name,
                'phone' => $user->phone
            ]
        ]);
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'Logout',
                'description' => $user->name . ' logged out.',
                'ip_address' => $request->ip()
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.'
        ]);
    }

    public function session()
    {
        $user = Auth::user();
        if ($user) {
            if (!$user->is_active) {
                Auth::logout();
                return response()->json([
                    'success' => false,
                    'user' => null,
                    'message' => 'Account is inactive.'
                ]);
            }

            return response()->json([
                'success' => true,
                'user' => [
                    'name' => $user->name,
                    'firstName' => $user->first_name,
                    'lastName' => $user->last_name,
                    'email' => $user->email,
                    'bdate' => $user->birthdate,
                    'affType' => $user->affiliation_type,
                    'affName' => $user->affiliation_name,
                    'phone' => $user->phone
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'user' => null
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email'
        ]);

        $email = strtolower($request->input('email'));
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No account found with this email.'
            ], 404);
        }

        // Return a mock reset response since we are running in local/XAMPP environment without a mail server.
        // We will do a funny/helpful response similar to the frontend's original action:
        return response()->json([
            'success' => true,
            'message' => 'An email search was performed. For security, passwords are encrypted, but in this development build, we can confirm the account exists! (Password recovery link has been simulated).'
        ]);
    }
}
