<?php

namespace App\Http\Controllers;

use App\Enums\ClaimStatus;
use App\Models\Claim;
use App\Models\Contribution;
use App\Models\Member;
use App\Models\PayoutTriggerType;
use App\Services\LedgerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class MemberPortalController extends Controller
{
    /**
     * Helper to get active member for currently logged-in user.
     */
    protected function getMember(): ?Member
    {
        $user = Auth::user();
        if (! $user) {
            return null;
        }

        return Member::with(['idir.settings', 'contributions' => fn ($q) => $q->latest(), 'claims.triggerType'])
            ->where('user_id', $user->id)
            ->first();
    }

    /**
     * Member Dashboard (Read-only payment history, claims status, and idir overview).
     */
    public function dashboard()
    {
        $member = $this->getMember();
        if (! $member) {
            return redirect('/committee');
        }

        if (! $member->idir->isActive()) {
            return response()->view('errors.suspended', ['idir' => $member->idir], 403);
        }

        $contributions = $member->contributions()->latest()->take(20)->get();
        $claims = $member->claims()->with(['triggerType', 'approvals'])->latest()->get();
        $isVested = app(LedgerService::class)->isVested($member);

        return view('member.dashboard', compact('member', 'contributions', 'claims', 'isVested'));
    }

    /**
     * Show File Claim form.
     */
    public function showClaimForm()
    {
        $member = $this->getMember();
        if (! $member) {
            return redirect('/committee');
        }

        $isVested = app(LedgerService::class)->isVested($member);
        $triggerTypes = PayoutTriggerType::where('idir_id', $member->idir_id)
            ->where('is_active', true)
            ->get();

        return view('member.file-claim', compact('member', 'isVested', 'triggerTypes'));
    }

    /**
     * Submit a claim from member portal.
     */
    public function submitClaim(Request $request)
    {
        $member = $this->getMember();
        if (! $member) {
            abort(403);
        }

        // Vesting check
        if (! app(LedgerService::class)->isVested($member)) {
            return back()->withErrors(['vesting' => __('member.vesting_not_met')]);
        }

        $validated = $request->validate([
            'payout_trigger_type_id' => [
                'required',
                // The trigger must belong to the member's own idir to prevent
                // cross-tenant references (foreign payout defaults leaking in).
                Rule::exists('payout_trigger_types', 'id')->where('idir_id', $member->idir_id),
            ],
            'requested_amount' => 'nullable|numeric|min:1',
            'description' => 'required|string|min:5',
            'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $documentPath = null;
        if ($request->hasFile('document')) {
            $documentPath = $request->file('document')->store('claims/documents', 'public');
        }

        Claim::create([
            'idir_id' => $member->idir_id,
            'member_id' => $member->id,
            'payout_trigger_type_id' => $validated['payout_trigger_type_id'],
            'requested_amount' => $validated['requested_amount'] ?? null,
            'description' => $validated['description'],
            'status' => ClaimStatus::Pending,
            'document_path' => $documentPath,
        ]);

        return redirect()->route('member.dashboard')->with('success', 'የክፍያ ጥያቄዎ ለኮሚቴው በተሳካ ሁኔታ ቀርቧል!');
    }

    /**
     * Show printable official Idir contribution receipt.
     *
     * Accessible only to the contributing member themselves, or to a committee
     * member of the same idir (who legitimately prints official receipts).
     */
    public function showReceipt(Contribution $contribution)
    {
        $contribution->load(['member', 'paidBy', 'recordedBy', 'idir.settings']);

        $viewer = $this->getMember();
        $isOwner = $contribution->member?->user_id === auth()->id();
        $isCommitteeOfIdir = $viewer !== null
            && $viewer->idir_id === $contribution->idir_id
            && $viewer->isCommitteeMember();

        abort_unless($isOwner || $isCommitteeOfIdir, 403);

        return view('member.receipt', [
            'contribution' => $contribution,
            'member' => $contribution->member,
            'idir' => $contribution->idir,
        ]);
    }
}
