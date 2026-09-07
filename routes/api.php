<?php

use App\Models\Member;
use App\Models\User;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

// Token generation endpoint for mobile / external client auth (Rate-Limited)
Route::post('/auth/token', function (Request $request) {
    $request->validate([
        'phone' => 'required',
        'password' => 'required',
        'device_name' => 'nullable|string',
    ]);

    $user = User::where('phone', $request->phone)
        ->orWhere('email', $request->phone)
        ->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        Log::warning('API Auth Failed: Invalid credentials', ['phone' => $request->phone, 'ip' => $request->ip()]);

        return response()->json(['message' => 'የተሰጠው ስልክ ቁጥር ወይም የይለፍ ቃል ትክክል አይደለም።'], 401);
    }

    $token = $user->createToken($request->device_name ?? 'mobile-app')->plainTextToken;

    Log::info('API Auth Succeeded: Token generated', ['user_id' => $user->id]);

    return response()->json([
        'token' => $token,
        'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'phone' => $user->phone,
        ],
    ]);
})->middleware('throttle:30,1');

// Protected Sanctum Routes (Rate-Limited)
Route::middleware(['auth:sanctum', 'throttle:120,1'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user()->load('idirs');
    });

    Route::get('/member/profile', function (Request $request) {
        $member = Member::with('idir.settings')->where('user_id', $request->user()->id)->firstOrFail();

        return response()->json($member);
    });

    Route::get('/member/contributions', function (Request $request) {
        $member = Member::where('user_id', $request->user()->id)->firstOrFail();

        return response()->json($member->contributions()->latest()->get());
    });

    Route::get('/member/claims', function (Request $request) {
        $member = Member::where('user_id', $request->user()->id)->firstOrFail();

        return response()->json($member->claims()->with('triggerType', 'approvals')->latest()->get());
    });
});

// Telegram Bot Webhook endpoint (Rate-Limited + Secret Header Verified)
Route::post('/telegram/webhook', function (Request $request, TelegramService $telegram) {
    $secret = config('services.telegram.webhook_secret');
    $headerToken = $request->header('X-Telegram-Bot-Api-Secret-Token');

    if (! empty($secret) && ! empty($headerToken) && $secret !== $headerToken) {
        Log::warning('Telegram Webhook: Invalid secret token rejected', ['ip' => $request->ip()]);

        return response()->json(['error' => 'Unauthorized'], 401);
    }

    $message = $request->input('message');
    if (! $message) {
        return response()->json(['status' => 'ignored'], 200);
    }

    $chatId = $message['chat']['id'] ?? null;
    $text = trim($message['text'] ?? '');

    // Handle /start LINK_CODE
    if (str_starts_with($text, '/start ') && $chatId) {
        $linkCode = substr($text, 7);
        $member = Member::where('phone', $linkCode)->orWhere('id', $linkCode)->first();

        if ($member) {
            $member->update(['telegram_chat_id' => (string) $chatId]);
            $telegram->sendMessage((string) $chatId, "ውድ {$member->full_name}፣ የቴሌግራም አካውንትዎ ከ {$member->idir->name} ጋር በተሳካ ሁኔታ ተገናኝቷል!");
            Log::info('Telegram Bot: Account linked', ['member_id' => $member->id, 'chat_id' => $chatId]);
        } else {
            $telegram->sendMessage((string) $chatId, 'ይቅርታ፣ የተሰጠው የማገናኛ ቁጥር አልተገኘም። እባክዎ ስልክ ቁጥርዎን ያስገቡ።');
        }
    }

    return response()->json(['status' => 'ok'], 200);
})->middleware('throttle:60,1');
