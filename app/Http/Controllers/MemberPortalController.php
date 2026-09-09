<?php

namespace App\Http\Controllers;

use App\Enums\ClaimStatus;
use App\Enums\NotificationType;
use App\Jobs\SendNotificationJob;
use App\Models\Claim;
use App\Models\Contribution;
use App\Models\Member;
use App\Models\PayoutTriggerType;
use App\Services\LedgerService;
use App\Services\Payments\Drivers\ChapaDriver;
use App\Services\Payments\PaymentGatewayManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
     * Show the member payment page with named online payment options
     * (Telebirr, CBE Birr) plus the generic Chapa hosted-checkout fallback.
     */
    public function showPayment()
    {
        $member = $this->getMember();
        if (! $member) {
            return redirect('/committee');
        }

        if (! $member->idir->isActive()) {
            return response()->view('errors.suspended', ['idir' => $member->idir], 403);
        }

        $dueAmount = (float) ($member->idir->settings?->dues_amount ?? config('idir.defaults.dues_amount'));

        return view('member.pay', compact('member', 'dueAmount'));
    }

    /**
     * Initiate an online contribution payment with the selected method.
     *
     * Telebirr and CBE Birr use Chapa's Direct Charge API: a USSD prompt is
     * pushed to the member's phone and the ledger entry stays pending until
     * Chapa's webhook confirms it. The generic Chapa option uses hosted
     * checkout and redirects to Chapa's page.
     */
    public function payWithMethod(Request $request, PaymentGatewayManager $gatewayManager)
    {
        $member = $this->getMember();
        if (! $member) {
            abort(403);
        }

        if (! $member->idir->isActive()) {
            return response()->view('errors.suspended', ['idir' => $member->idir], 403);
        }

        $validated = $request->validate([
            'method' => ['required', Rule::in(['telebirr', 'cbebirr', 'chapa'])],
            'mobile' => ['required_if:method,telebirr,cbebirr', 'nullable', 'string', 'regex:/^[+0-9][0-9\s-]{8,16}$/'],
        ]);

        $amount = (float) ($member->idir->settings?->dues_amount ?? config('idir.defaults.dues_amount'));
        $period = now()->format('Y-m');

        /** @var ChapaDriver $driver */
        $driver = $gatewayManager->driver('chapa');

        if ($validated['method'] === 'chapa') {
            $response = $driver->initialize($member, $amount, $period);

            $checkoutUrl = $response['data']['checkout_url'] ?? null;

            if (($response['status'] ?? '') !== 'success' || ! $checkoutUrl) {
                Log::warning('Member payment: Chapa hosted checkout failed', [
                    'member_id' => $member->id,
                    'response' => $response,
                ]);

                return back()->withErrors(['payment' => __('contribution.payment_init_failed')]);
            }

            return redirect()->away($checkoutUrl);
        }

        $mobile = preg_replace('/[^0-9+]/', '', $validated['mobile'] ?? '');
        $response = $driver->initializeDirectCharge($member, $amount, $period, $validated['method'], $mobile);

        if (($response['status'] ?? '') !== 'success') {
            return back()->withErrors(['payment' => $response['message'] ?? __('contribution.payment_init_failed')]);
        }

        $contribution = Contribution::where('member_id', $member->id)
            ->where('chapa_status', 'pending')
            ->latest()
            ->first();

        return redirect()->route('member.payment.pending', $contribution);
    }

    /**
     * Show the pending confirmation screen after a Direct Charge is initiated:
     * the USSD prompt is on its way to the member's phone and the ledger entry
     * is pending until Chapa's webhook confirms the payment.
     */
    public function showPaymentPending(Contribution $contribution)
    {
        $member = $this->getMember();
        if (! $member || $contribution->member_id !== $member->id) {
            abort(403);
        }

        return view('member.payment-pending', compact('member', 'contribution'));
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

        $claim = Claim::create([
            'idir_id' => $member->idir_id,
            'member_id' => $member->id,
            'payout_trigger_type_id' => $validated['payout_trigger_type_id'],
            'requested_amount' => $validated['requested_amount'] ?? null,
            'description' => $validated['description'],
            'status' => ClaimStatus::Pending,
            'document_path' => $documentPath,
        ]);

        // Notify every committee approver that a new claim awaits their review.
        foreach ($member->idir->committeeMembers as $approver) {
            SendNotificationJob::dispatch(
                $claim->idir_id,
                $approver->id,
                NotificationType::ClaimFiled,
            );
        }

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
