<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccessRequestController extends Controller
{
    /**
     * Display access request form or current status.
     */
    public function show(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('register');
        }

        if (! $user->isPhoneVerified()) {
            return redirect()->route('phone.verify');
        }

        // Spec: the access-request flow is for phone-verified users WITHOUT can_create_idir.
        // Users who already have the capability should not see the form; bounce them to the
        // wizard (consistent with the store() guard).
        if ($user->canCreateIdir()) {
            return redirect('/committee/new');
        }

        // If user already belongs to an idir, redirect them to their committee dashboard
        if ($user->idirs()->exists()) {
            return redirect('/committee');
        }

        $latestRequest = $user->latestAccessRequest;
        $reapply = $request->boolean('reapply', false);

        return view('auth.access-request', [
            'user' => $user,
            'latestRequest' => $latestRequest,
            'reapply' => $reapply,
        ]);
    }

    /**
     * Submit a new manager access request.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('register');
        }

        if (! $user->isPhoneVerified()) {
            return redirect()->route('phone.verify');
        }

        if ($user->canCreateIdir()) {
            return redirect('/committee/new');
        }

        $latestRequest = $user->latestAccessRequest;
        if ($latestRequest && $latestRequest->isPending()) {
            return redirect()->route('access-request')
                ->with('warning', 'ጥያቄዎ ቀደም ሲል የቀረበ ሲሆን በግምገማ ላይ ይገኛል።');
        }

        $validated = $request->validate([
            'idir_name' => ['required', 'string', 'max:255'],
            'membership_basis' => ['nullable', 'string', 'max:255'],
            'region' => ['nullable', 'string', 'max:255'],
            'sub_city' => ['nullable', 'string', 'max:255'],
            'purpose' => ['nullable', 'string', 'max:2000'],
        ], [
            'idir_name.required' => 'እባክዎ ሊመሠርቱት ያሰቡትን የእድር ስም ያስገቡ።',
        ]);

        $user->accessRequests()->create([
            'idir_name' => $validated['idir_name'],
            'membership_basis' => $validated['membership_basis'] ?? null,
            'region' => $validated['region'] ?? null,
            'sub_city' => $validated['sub_city'] ?? null,
            'purpose' => $validated['purpose'] ?? null,
            'status' => 'pending',
        ]);

        return redirect()->route('access-request')
            ->with('status', 'የማኔጀርነት ፈቃድ ጥያቄዎ በተሳካ ሁኔታ ቀርቧል! የፕላትፎርም ባለቤቱ ሲገመግሙት በኤስኤምኤስ ይደርስዎታል።');
    }
}
