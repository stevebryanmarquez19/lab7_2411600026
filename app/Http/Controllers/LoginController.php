<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    private const DEMO_ACCOUNTS = [
        'admin'     => ['name' => 'Admin',             'email' => 'admin@fitpulse.local',     'role' => 'admin'],
        'manager'   => ['name' => 'Maria Manager',     'email' => 'manager@fitpulse.local',   'role' => 'manager'],
        'warehouse' => ['name' => 'Wally Warehouse',   'email' => 'warehouse@fitpulse.local', 'role' => 'warehouse'],
        'staff'     => ['name' => 'Sam Staff',         'email' => 'staff@fitpulse.local',     'role' => 'staff'],
    ];

    public function show()
    {
        if (session('fitpulse_authenticated')) {
            return redirect()->route('dashboard');
        }
        return view('login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string|max:100',
            'password' => 'required|string|max:255',
        ]);

        // Demo accounts. 'admin' is the original Lab 4/6 login; the others exist to test the Lab 7 approval workflow.
        $account = self::DEMO_ACCOUNTS[$credentials['username']] ?? null;
        if (!$account || $credentials['password'] !== 'password123') {
            return back()->withErrors(['username' => 'Incorrect username or password.'])->withInput($request->only('username'));
        }

        $user = User::firstOrCreate(
            ['email' => $account['email']],
            ['name' => $account['name'], 'password' => Hash::make('password123'), 'role' => $account['role']]
        );
        if (!$user->password) {
            $user->password = Hash::make('password123');
        }
        $user->role = $account['role']; // keep the role in sync with the demo account
        if ($user->isDirty()) {
            $user->save();
        }

        $request->session()->regenerate();
        Auth::login($user);
        $request->session()->put('fitpulse_authenticated', true);
        $request->session()->put('fitpulse_user', $credentials['username']);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->forget(['fitpulse_authenticated', 'fitpulse_user']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('login_notice', 'You have been logged out.');
    }
}
