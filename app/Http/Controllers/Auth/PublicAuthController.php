<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PublicAuthController extends Controller
{
    /**
     * Show public signup form.
     */
    public function showSignupForm()
    {
        if (Auth::check()) {
            $user = Auth::user();
            if (!$user->isPhoneVerified()) {
                return redirect()->route('phone.verify');
            }
            return redirect('/committee');
        }

        return view('auth.register');
    }

    /**
     * Handle public signup request.
     */
    public function register(Request $request, OtpService $otpService)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => [
                'required',
                'string',
                'regex:/^(?:\+251|0)[97]\d{8}$/',
                'unique:users,phone',
            ],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'name.required' => 'እባክዎ ሙሉ ስምዎን ያስገቡ።',
            'phone.required' => 'እባክዎ ትክክለኛ የኢትዮጵያ ስልክ ቁጥር ያስገቡ።',
            'phone.regex' => 'ስልክ ቁጥር በ 09 ወይም 07 መጀመር አለበት (ለምሳሌ 0911223344)።',
            'phone.unique' => 'ይህ ስልክ ቁጥር አስቀድሞ ተመዝግቧል።',
            'password.required' => 'የይለፍ ቃል ያስፈልጋል።',
            'password.min' => 'የይለፍ ቃል ቢያንስ 8 ፊደላት/ቁጥሮች መሆን አለበት።',
            'password.confirmed' => 'የይለፍ ቃል ማረጋገጫው አልተዛመደም።',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'password' => Hash::make($validated['password']),
            'phone_verified_at' => null,
            'is_platform_owner' => false,
        ]);

        Auth::login($user);

        // Send OTP via AfroMessage
        $otpService->sendOtp($user);

        return redirect()->route('phone.verify')
            ->with('status', 'የማረጋገጫ ኮድ በኤስኤምኤስ (SMS) ወደ ስልክዎ ተልኳል።');
    }

    /**
     * Show phone verification view.
     */
    public function showVerifyPhoneForm()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('register');
        }

        if ($user->isPhoneVerified()) {
            return redirect('/committee/new');
        }

        return view('auth.verify-phone', [
            'user' => $user,
        ]);
    }

    /**
     * Verify submitted OTP code.
     */
    public function verifyPhone(Request $request, OtpService $otpService)
    {
        $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ], [
            'code.required' => 'እባክዎ 6 አሃዝ የማረጋገጫ ኮድ ያስገቡ።',
            'code.size' => 'የማረጋገጫ ኮዱ 6 አሃዝ መሆን አለበት።',
        ]);

        $user = Auth::user();

        if ($otpService->verifyOtp($user, $request->code)) {
            return redirect('/committee/new')
                ->with('success', 'ስልክዎ በተሳካ ሁኔታ ተረጋግጧል! አሁን አዲሱን እድርዎን መመዝገብ ይችላሉ።');
        }

        return back()->withErrors([
            'code' => 'የተሳሳተ ወይም ጊዜው ያለፈበት የማረጋገጫ ኮድ። እባክዎ እንደገና ይሞክሩ።',
        ]);
    }

    /**
     * Resend verification OTP code.
     */
    public function resendOtp(OtpService $otpService)
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('register');
        }

        $otpService->sendOtp($user);

        return back()->with('status', 'አዲስ የማረጋገጫ ኮድ በድጋሚ ተልኳል።');
    }
}
