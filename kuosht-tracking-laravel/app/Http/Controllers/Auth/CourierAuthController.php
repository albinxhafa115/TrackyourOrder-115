<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Courier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CourierAuthController extends Controller
{
    /**
     * Show courier login form
     */
    public function showLogin()
    {
        return view('courier.login');
    }

    /**
     * Handle courier login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $courier = Courier::where('email', $request->email)->first();

        if (!$courier || !Hash::check($request->password, $courier->password_hash)) {
            throw ValidationException::withMessages([
                'email' => ['Email ose fjalëkalimi është i gabuar.'],
            ]);
        }

        // Login courier
        Auth::guard('courier')->login($courier, $request->boolean('remember'));

        $request->session()->regenerate();

        return redirect()->intended(route('courier.dashboard'));
    }

    /**
     * Handle courier logout
     */
    public function logout(Request $request)
    {
        Auth::guard('courier')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('courier.login');
    }

    /**
     * Get authenticated courier
     */
    public function me()
    {
        return response()->json([
            'courier' => Auth::guard('courier')->user()
        ]);
    }
}
