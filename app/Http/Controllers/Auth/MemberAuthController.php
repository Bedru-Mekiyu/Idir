<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class MemberAuthController extends Controller
{
    /**
     * Show member login form.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('member.dashboard');
        }

        return view('member.login');
    }

    /**
     * Handle member login request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $loginInput = trim($credentials['login']);

        // Find user by phone, email, or normalized phone
        $user = User::where('phone', $loginInput)
            ->orWhere('email', $loginInput)
            ->first();

        if (! $user) {
            // Also try matching member's phone and linking user if exists
            $member = Member::where('phone', $loginInput)->first();
            if ($member && $member->user_id) {
                $user = User::find($member->user_id);
            }
        }

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'login' => [__('auth.failed')],
            ]);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('member.dashboard'));
    }

    /**
     * Log the member out.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('member.login')->with('success', 'በተሳካ ሁኔታ ወጥተዋል።');
    }
}
