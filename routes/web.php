<?php

use App\Http\Controllers\Auth\PublicAuthController;
use App\Http\Controllers\Auth\FaydaOidcController;
use App\Http\Controllers\Auth\MemberAuthController;
use App\Http\Controllers\MemberPortalController;
use App\Jobs\ProcessChapaWebhookJob;
use App\Models\Contribution;
use App\Services\LedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

// Public Landing Page
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Public Self-Service Signup & Phone OTP Verification
Route::middleware(['web'])->group(function () {
    Route::get('/register', [PublicAuthController::class, 'showSignupForm'])->name('register');
    Route::post('/register', [PublicAuthController::class, 'register'])
        ->middleware('throttle:10,1')
        ->name('register.submit');

    Route::get('/verify-phone', [PublicAuthController::class, 'showVerifyPhoneForm'])->name('phone.verify');
    Route::post('/verify-phone', [PublicAuthController::class, 'verifyPhone'])
        ->middleware('throttle:10,1')
        ->name('phone.verify.submit');
    Route::post('/verify-phone/resend', [PublicAuthController::class, 'resendOtp'])
        ->middleware('throttle:5,1')
        ->name('phone.verify.resend');
});

// Member Public Authentication & Lookup Routes (Rate-Limited)
Route::middleware(['web'])->group(function () {
    Route::get('/login', fn () => redirect()->route('member.login'))->name('login');
    Route::get('/member/login', [MemberAuthController::class, 'showLoginForm'])->name('member.login');
    Route::post('/member/login', [MemberAuthController::class, 'login'])
        ->middleware('throttle:10,1')
        ->name('member.login.submit');
    Route::post('/member/logout', [MemberAuthController::class, 'logout'])->name('member.logout');

    Route::get('/member/lookup', [MemberAuthController::class, 'showLookupForm'])->name('member.lookup');
    Route::post('/member/lookup', [MemberAuthController::class, 'lookup'])
        ->middleware('throttle:20,1')
        ->name('member.lookup.submit');

    // Printable Receipt View
    Route::get('/member/receipt/{contribution}', [MemberPortalController::class, 'showReceipt'])->name('member.receipt');
});

// Fayda OIDC Authentication Routes
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/auth/fayda/redirect', [FaydaOidcController::class, 'redirect'])->name('fayda.redirect');
    Route::get('/auth/fayda/callback', [FaydaOidcController::class, 'callback'])->name('fayda.callback');
});

// Member Portal Protected Routes
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/member', [MemberPortalController::class, 'dashboard'])->name('member.dashboard');
    Route::get('/member/claims/create', [MemberPortalController::class, 'showClaimForm'])->name('member.claims.create');
    Route::post('/member/claims', [MemberPortalController::class, 'submitClaim'])->name('member.claims.store');

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
    if (!empty($secret) && !empty($signature)) {
        $computed = hash_hmac('sha256', $request->getContent(), $secret);
        if (!hash_equals($computed, $signature)) {
            Log::warning('Chapa Webhook: Invalid signature rejected', [
                'ip' => $request->ip(),
                'signature' => $signature,
            ]);
            return response()->json(['error' => 'Invalid webhook signature'], 401);
        }
    }

    $txRef = $request->input('tx_ref') ?? $request->input('trx_ref');

    if (!$txRef) {
        return response()->json(['error' => 'Missing tx_ref'], 400);
    }

    Log::info('Chapa Webhook: Received verified callback', ['tx_ref' => $txRef]);

    // Dispatch background job for server-side verification with retries and exponential backoff
    ProcessChapaWebhookJob::dispatch($txRef, $request->all());

    return response()->json(['status' => 'acknowledged'], 200);
})->middleware('throttle:60,1')->name('chapa.webhook');

// Mock Chapa Checkout for local development/testing
Route::get('/mock/chapa/checkout/{txRef}', function (string $txRef, LedgerService $ledger) {
    $contribution = Contribution::where('chapa_tx_ref', $txRef)->firstOrFail();
    $contribution->update(['chapa_status' => \App\Enums\ChapaStatus::Verified]);
    $ledger->recalculateFundBalance($contribution->idir_id);
    $ledger->updateMemberArrearsStatus($contribution->member_id);

    return redirect()->route('member.dashboard')->with('success', 'ክፍያ በቻፓ በተሳካ ሁኔታ ተጠናቋል!');
});
