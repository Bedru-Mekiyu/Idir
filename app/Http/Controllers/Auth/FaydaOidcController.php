<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Services\FaydaOidcService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FaydaOidcController extends Controller
{
    /**
     * Redirect member to Fayda eSignet OIDC authorization portal.
     */
    public function redirect(Request $request, FaydaOidcService $fayda)
    {
        $state = bin2hex(random_bytes(16));
        $nonce = bin2hex(random_bytes(16));

        $request->session()->put('fayda_state', $state);
        $request->session()->put('fayda_nonce', $nonce);

        return redirect()->away($fayda->getAuthorizationUrl($state, $nonce));
    }

    /**
     * Handle OIDC callback from Fayda eSignet.
     */
    public function callback(Request $request, FaydaOidcService $fayda)
    {
        $code = $request->query('code');
        $state = $request->query('state');
        $savedState = $request->session()->pull('fayda_state');

        if (!$code || ($state && $savedState && $state !== $savedState)) {
            return redirect()->route('member.dashboard')->withErrors([
                'fayda' => 'የፋይዳ ማረጋገጫ አልተሳካም። እባክዎ እንደገና ይሞክሩ።',
            ]);
        }

        $user = Auth::user();
        $member = $user?->member;

        if (!$member) {
            return redirect()->route('member.dashboard');
        }

        $success = $fayda->verifyAndLinkMember($member, $code);

        if ($success) {
            return redirect()->route('member.dashboard')->with('success', 'የፋይዳ ብሔራዊ መታወቂያዎ በተሳካ ሁኔታ ተረጋግጧል!');
        }

        return redirect()->route('member.dashboard')->withErrors([
            'fayda' => 'የፋይዳ መረጃዎችን ማረጋገጥ አልተቻለም።',
        ]);
    }
}
