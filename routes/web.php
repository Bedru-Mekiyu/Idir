<?php

use App\Enums\ChapaStatus;
use App\Http\Controllers\AccessRequestController;
use App\Http\Controllers\Auth\FaydaOidcController;
use App\Http\Controllers\Auth\MemberAuthController;
use App\Http\Controllers\Auth\PublicAuthController;
use App\Http\Controllers\MemberPortalController;
use App\Models\Contribution;
use App\Services\LedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\MemberProfileController;
use Illuminate\Support\Facades\Route;

// Public Landing Page
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Public Self-Service Signup & Phone OTP Verification
Route::middleware(['web'])->group(function () {
    Route::get('/register', [PublicAuthController::class, 'showSignupForm'])->name('register');
    Route::post('/register', [PublicAuthController::class, 'register'])
        ->middleware('throttle:register')
        ->name('register.submit');

    Route::get('/verify-phone', [PublicAuthController::class, 'showVerifyPhoneForm'])->name('phone.verify');
    Route::post('/verify-phone', [PublicAuthController::class, 'verifyPhone'])
        ->middleware('throttle:phone-verify')
        ->name('phone.verify.submit');
    Route::post('/verify-phone/resend', [PublicAuthController::class, 'resendOtp'])
        ->middleware('throttle:otp-resend')
        ->name('phone.verify.resend');

    // Manager Access Request Routes
    Route::get('/access-request', [AccessRequestController::class, 'show'])->name('access-request');
    Route::post('/access-request', [AccessRequestController::class, 'store'])
        ->middleware('throttle:access-request')
        ->name('access-request.store');
});

// Member Public Authentication & Lookup Routes (Rate-Limited)
Route::middleware(['web'])->group(function () {
    Route::get('/login', fn () => redirect()->route('member.login'))->name('login');
    Route::get('/member/login', [MemberAuthController::class, 'showLoginForm'])->name('member.login');
    Route::post('/member/login', [MemberAuthController::class, 'login'])
        ->middleware('throttle:member-login')
        ->name('member.login.submit');
    Route::post('/member/logout', [MemberAuthController::class, 'logout'])->name('member.logout');

    // Deprecated public lookup: strictly redirected to member.login to protect member privacy
    Route::get('/lookup', fn () => redirect()->route('member.login'));
    Route::get('/member/lookup', fn () => redirect()->route('member.login'))->name('member.lookup');
    Route::post('/member/lookup', fn () => redirect()->route('member.login'));
});

// Fayda OIDC Authentication Routes
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/auth/fayda/redirect', [FaydaOidcController::class, 'redirect'])->name('fayda.redirect');
    Route::get('/auth/fayda/callback', [FaydaOidcController::class, 'callback'])->name('fayda.callback');
});

// Member Portal Protected Routes
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/member', [MemberPortalController::class, 'dashboard'])->name('member.dashboard');
    Route::get('/member/profile', [MemberProfileController::class, 'show'])->name('member.profile');
    Route::post('/member/profile', [MemberProfileController::class, 'update'])->name('member.profile.update');
    Route::get('/member/claims/create', [MemberPortalController::class, 'showClaimForm'])->name('member.claims.create');
    Route::post('/member/claims', [MemberPortalController::class, 'submitClaim'])->name('member.claims.store');

    // Printable Official Receipt (authenticated; ownership/committee checked in controller)
    Route::get('/member/receipt/{contribution}', [MemberPortalController::class, 'showReceipt'])->name('member.receipt');

    Route::get('/payment/success', function () {
        return view('layouts.member', [
            'member' => auth()->user()?->member,
        ]);
    })->name('payment.success');
});

// Chapa Webhook Handler (Signature Verified + Rate-Limited + Async Queued)
Route::post('/api/chapa/webhook', function (Request $request) {
    $secret = config('services.chapa.webhook_secret');
    $signature = $request->header('x-chapa-signature') ?? $request->header('chapa-signature');

    // Webhook signature verification
    if (! empty($secret)) {
        if (empty($signature)) {
            Log::warning('Chapa Webhook: Missing signature rejected', ['ip' => $request->ip()]);

            return response()->json(['error' => 'Missing webhook signature'], 401);
        }

        $computed = hash_hmac('sha256', $request->getContent(), $secret);
        if (! hash_equals($computed, $signature)) {
            Log::warning('Chapa Webhook: Invalid signature rejected', [
                'ip' => $request->ip(),
                'signature' => $signature,
            ]);

            return response()->json(['error' => 'Invalid webhook signature'], 401);
        }
    }

    $txRef = $request->input('tx_ref') ?? $request->input('trx_ref');

    if (! $txRef) {
        return response()->json(['error' => 'Missing tx_ref'], 400);
    }

    Log::info('Chapa Webhook: Received verified callback', ['tx_ref' => $txRef]);

    // Dispatch background job for server-side verification with retries and exponential backoff
    \App\Jobs\ProcessPaymentWebhookJob::dispatch('chapa', $txRef, $request->all());

    return response()->json(['status' => 'acknowledged'], 200);
})->middleware('throttle:60,1')->name('chapa.webhook');
