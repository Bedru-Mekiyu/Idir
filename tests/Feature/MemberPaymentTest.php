<?php

namespace Tests\Feature;

use App\Enums\ChapaStatus;
use App\Enums\DuesFrequency;
use App\Enums\MemberStatus;
use App\Enums\PaymentMethod;
use App\Models\Contribution;
use App\Models\Idir;
use App\Models\IdirSetting;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MemberPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected Idir $idir;

    protected User $user;

    protected Member $member;

    protected function setUp(): void
    {
        parent::setUp();

        // Deterministic clock so the generated `period_covered` (Y-m) is stable.
        $this->travelTo('2026-09-08 12:00:00');

        $this->idir = Idir::create([
            'name' => 'ሰላም የሰፈር እድር',
            'locale' => 'am',
        ]);

        IdirSetting::create([
            'idir_id' => $this->idir->id,
            'dues_amount' => 200.00,
            'dues_frequency' => DuesFrequency::Monthly,
            'vesting_period_days' => 90,
            'required_approvals' => 2,
            'fund_balance' => 0.00,
            'chapa_subaccount_id' => 'sub-acct-member-001',
        ]);

        $this->user = User::create([
            'name' => 'ሙሉጌታ ኃይለ ማርያም',
            'email' => 'mulugeta@example.com',
            'phone' => '0911556677',
            'password' => bcrypt('password'),
        ]);

        $this->member = Member::create([
            'idir_id' => $this->idir->id,
            'user_id' => $this->user->id,
            'full_name' => $this->user->name,
            'phone' => $this->user->phone,
            'join_date' => now()->subMonths(6)->toDateString(),
            'status' => MemberStatus::Active,
        ]);
    }

    public function test_guest_is_redirected_to_login_from_pay_page(): void
    {
        $this->get('/member/pay')->assertRedirect(route('member.login'));
    }

    public function test_pay_page_renders_named_payment_options(): void
    {
        $response = $this->actingAs($this->user)->get('/member/pay');

        $response->assertOk();
        $response->assertSee('በቴሌብር ይክፈሉ');
        $response->assertSee('በሲቢኢ ብር ይክፈሉ');
        $response->assertSee('በሌሎች ዘዴዎች ይክፈሉ');
        $response->assertSee('200.00');
    }

    public function test_pay_page_shows_configured_subaccount_to_member(): void
    {
        $response = $this->actingAs($this->user)->get('/member/pay');

        $response->assertOk();
        $response->assertSee('sub-acct-member-001');
    }

    public function test_pay_page_hides_subaccount_note_when_not_configured(): void
    {
        $this->idir->settings()->update(['chapa_subaccount_id' => null]);

        $response = $this->actingAs($this->user)->get('/member/pay');

        $response->assertOk();
        $response->assertDontSee('sub-acct-member-001');
    }

    public function test_pay_with_telebirr_creates_pending_contribution_and_redirects_to_pending_page(): void
    {
        Http::fake([
            'api.chapa.co/v1/charges*' => Http::response([
                'status' => 'success',
                'message' => 'Charge initiated',
                'data' => ['reference' => 'CHcuKjgnN0Dk0', 'status' => 'pending'],
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->post('/member/pay', [
            'method' => 'telebirr',
            'mobile' => '0911556677',
        ]);

        $contribution = Contribution::where('member_id', $this->member->id)->first();

        $this->assertNotNull($contribution);
        $this->assertEquals(PaymentMethod::Telebirr, $contribution->method);
        $this->assertEquals(ChapaStatus::Pending, $contribution->chapa_status);
        $this->assertEquals(200.00, $contribution->amount);
        $this->assertEquals('2026-09', $contribution->period_covered);

        $response->assertRedirectToRoute('member.payment.pending', ['contribution' => $contribution]);

        Http::assertSent(fn ($request) => str_contains($request->url(), 'charges?type=telebirr')
            && $request['mobile'] === '0911556677'
            && $request['tx_ref'] === $contribution->chapa_tx_ref
            && $request['subaccounts'] === json_encode(['id' => 'sub-acct-member-001']));
    }

    public function test_pay_with_cbebirr_creates_pending_contribution(): void
    {
        Http::fake([
            'api.chapa.co/v1/charges*' => Http::response([
                'status' => 'success',
                'message' => 'Charge initiated',
                'data' => ['reference' => 'CHcbebirr0001', 'status' => 'pending'],
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->post('/member/pay', [
            'method' => 'cbebirr',
            'mobile' => '0911556677',
        ]);

        $contribution = Contribution::where('member_id', $this->member->id)->first();

        $this->assertNotNull($contribution);
        $this->assertEquals(PaymentMethod::CBEBirr, $contribution->method);
        $this->assertEquals(ChapaStatus::Pending, $contribution->chapa_status);

        $response->assertRedirectToRoute('member.payment.pending', ['contribution' => $contribution]);
    }

    public function test_pay_with_chapa_redirects_to_hosted_checkout(): void
    {
        Http::fake([
            'api.chapa.co/v1/transaction/initialize' => Http::response([
                'status' => 'success',
                'message' => 'Hosted Link',
                'data' => ['checkout_url' => 'https://checkout.chapa.co/checkout/real-url'],
            ], 200),
        ]);

        $response = $this->actingAs($this->user)->post('/member/pay', [
            'method' => 'chapa',
        ]);

        $response->assertRedirect('https://checkout.chapa.co/checkout/real-url');

        $contribution = Contribution::where('member_id', $this->member->id)->first();
        $this->assertNotNull($contribution);
        $this->assertEquals(PaymentMethod::Chapa, $contribution->method);
        $this->assertEquals(ChapaStatus::Pending, $contribution->chapa_status);

        Http::assertSent(fn ($request) => $request->url() === 'https://api.chapa.co/v1/transaction/initialize'
            && $request['subaccounts']['id'] === 'sub-acct-member-001');
    }

    public function test_pay_requires_mobile_for_direct_charge_methods(): void
    {
        $response = $this->actingAs($this->user)->post('/member/pay', [
            'method' => 'telebirr',
        ]);

        $response->assertSessionHasErrors('mobile');
        $this->assertDatabaseCount('contributions', 0);
    }

    public function test_pay_rejects_unknown_method(): void
    {
        $response = $this->actingAs($this->user)->post('/member/pay', [
            'method' => 'paypal',
        ]);

        $response->assertSessionHasErrors('method');
        $this->assertDatabaseCount('contributions', 0);
    }

    public function test_failed_direct_charge_initiation_returns_error_and_marks_contribution_failed(): void
    {
        Http::fake([
            'api.chapa.co/v1/charges*' => Http::response([
                'status' => 'failed',
                'message' => 'Invalid phone number',
                'data' => null,
            ], 400),
        ]);

        $response = $this->actingAs($this->user)->post('/member/pay', [
            'method' => 'telebirr',
            'mobile' => '0999999999',
        ]);

        $response->assertSessionHasErrors('payment');

        $contribution = Contribution::where('member_id', $this->member->id)->first();
        $this->assertNotNull($contribution);
        $this->assertEquals(ChapaStatus::Failed, $contribution->chapa_status);
    }

    public function test_pending_page_shows_pending_contribution_details(): void
    {
        $contribution = Contribution::create([
            'idir_id' => $this->idir->id,
            'member_id' => $this->member->id,
            'amount' => 200.00,
            'method' => PaymentMethod::Telebirr,
            'period_covered' => '2026-09',
            'chapa_tx_ref' => 'IDIR-PENDING-VIEW-TEST',
            'chapa_status' => ChapaStatus::Pending,
        ]);

        $response = $this->actingAs($this->user)->get(route('member.payment.pending', $contribution));

        $response->assertOk();
        $response->assertSee('የክፍያ ጥያቄ ተልኳል');
        $response->assertSee('ቴሌብር');
        $response->assertSee('IDIR-PENDING-VIEW-TEST');
    }

    public function test_pending_page_blocks_other_members(): void
    {
        $otherIdir = Idir::create(['name' => 'ሌላ እድር', 'locale' => 'am']);

        $otherMember = Member::create([
            'idir_id' => $otherIdir->id,
            'full_name' => 'ሌላ አባል',
            'phone' => '0922445566',
            'join_date' => '2023-01-01',
            'status' => MemberStatus::Active,
        ]);

        $contribution = Contribution::create([
            'idir_id' => $otherIdir->id,
            'member_id' => $otherMember->id,
            'amount' => 200.00,
            'method' => PaymentMethod::Telebirr,
            'period_covered' => '2026-09',
            'chapa_tx_ref' => 'IDIR-FOREIGN-PENDING',
            'chapa_status' => ChapaStatus::Pending,
        ]);

        $this->actingAs($this->user)->get(route('member.payment.pending', $contribution))->assertForbidden();
    }

    public function test_pending_page_redirects_guest_to_login(): void
    {
        $contribution = Contribution::create([
            'idir_id' => $this->idir->id,
            'member_id' => $this->member->id,
            'amount' => 200.00,
            'method' => PaymentMethod::Telebirr,
            'period_covered' => '2026-09',
            'chapa_tx_ref' => 'IDIR-GUEST-PENDING',
            'chapa_status' => ChapaStatus::Pending,
        ]);

        $this->get(route('member.payment.pending', $contribution))->assertRedirect(route('member.login'));
    }
}
