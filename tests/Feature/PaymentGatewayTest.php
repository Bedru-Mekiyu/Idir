<?php

namespace Tests\Feature;

use App\Enums\ChapaStatus;
use App\Enums\ContributionType;
use App\Enums\DuesFrequency;
use App\Enums\PaymentMethod;
use App\Jobs\ProcessPaymentWebhookJob;
use App\Models\Contribution;
use App\Models\Idir;
use App\Models\IdirSetting;
use App\Models\Member;
use App\Services\LedgerService;
use App\Services\Payments\PaymentGatewayManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaymentGatewayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.chapa.secret_key' => 'test_secret_key']);
        config(['services.chapa.webhook_secret' => 'test_webhook_secret']);
    }

    public function test_successful_payment_flow()
    {
        $idir = Idir::create(['name' => 'Test Idir']);
        $member = Member::create(['idir_id' => $idir->id, 'phone' => '0911000000', 'full_name' => 'John Doe', 'join_date' => '2023-01-01']);

        $gatewayManager = $this->app->make(PaymentGatewayManager::class);
        $driver = $gatewayManager->driver('chapa');

        $txRef = 'IDIR-'.$idir->id.'-'.$member->id.'-fakeuuid';

        Http::fake([
            'api.chapa.co/v1/transaction/initialize' => Http::response([
                'status' => 'success',
                'message' => 'Hosted Link',
                'data' => ['checkout_url' => 'https://checkout.chapa.co/checkout/fake-url'],
            ], 200),
            'api.chapa.co/v1/transaction/verify/*' => Http::response([
                'status' => 'success',
                'data' => [
                    'status' => 'success',
                    'tx_ref' => $txRef,
                    'currency' => 'ETB',
                    'amount' => 100,
                ],
            ], 200),
        ]);

        $response = $driver->initialize($member, 100, '2023-01');
        $this->assertEquals('success', $response['status']);

        // Assert pending contribution created
        $contribution = Contribution::where('member_id', $member->id)->first();
        $this->assertNotNull($contribution);
        $this->assertEquals(ChapaStatus::Pending, $contribution->chapa_status);
        $this->assertEquals(100, $contribution->amount);
        $txRefCreated = $contribution->chapa_tx_ref;

        // Process webhook job
        $job = new ProcessPaymentWebhookJob('chapa', $txRefCreated, []);
        $job->handle($gatewayManager, $this->app->make(LedgerService::class));

        // Assert verified
        $contribution->refresh();
        $this->assertEquals(ChapaStatus::Verified, $contribution->chapa_status);
    }

    public function test_network_timeout_during_initialization()
    {
        $idir = Idir::create(['name' => 'Test Idir']);
        $member = Member::create(['idir_id' => $idir->id, 'phone' => '0911000000', 'full_name' => 'John Doe', 'join_date' => '2023-01-01']);

        $gatewayManager = $this->app->make(PaymentGatewayManager::class);
        $driver = $gatewayManager->driver('chapa');

        Http::fake([
            'api.chapa.co/v1/transaction/initialize' => function () {
                throw new ConnectionException('Timeout');
            },
        ]);

        $this->expectException(ConnectionException::class);
        $driver->initialize($member, 100, '2023-01');
    }

    public function test_duplicate_webhook_payload_is_safely_ignored()
    {
        $idir = Idir::create(['name' => 'Test Idir']);
        $member = Member::create(['idir_id' => $idir->id, 'phone' => '0911000000', 'full_name' => 'John Doe', 'join_date' => '2023-01-01']);

        $gatewayManager = $this->app->make(PaymentGatewayManager::class);
        $driver = $gatewayManager->driver('chapa');

        $txRef = 'IDIR-'.$idir->id.'-'.$member->id.'-fakeuuid';

        // Pre-create verified contribution
        Contribution::create([
            'idir_id' => $idir->id,
            'member_id' => $member->id,
            'amount' => 100,
            'method' => PaymentMethod::Chapa,
            'type' => ContributionType::Cash,
            'period_covered' => '2023-01',
            'chapa_tx_ref' => $txRef,
            'chapa_status' => ChapaStatus::Verified,
        ]);

        Http::fake([
            'api.chapa.co/v1/transaction/verify/*' => Http::response([
                'status' => 'success',
                'data' => [
                    'status' => 'success',
                    'tx_ref' => $txRef,
                    'currency' => 'ETB',
                    'amount' => 100,
                ],
            ], 200),
        ]);

        // Attempt to process again
        $job = new ProcessPaymentWebhookJob('chapa', $txRef, []);
        $job->handle($gatewayManager, $this->app->make(LedgerService::class));

        // Find contribution again to make sure no duplicate was created
        $count = Contribution::where('chapa_tx_ref', $txRef)->count();
        $this->assertEquals(1, $count);
    }

    public function test_telebirr_direct_charge_creates_pending_contribution_and_posts_to_chapa(): void
    {
        [$idir, $member] = $this->directChargeFixture('sub-acct-telebirr-001');

        $gatewayManager = $this->app->make(PaymentGatewayManager::class);
        $driver = $gatewayManager->driver('chapa');

        Http::fake([
            'api.chapa.co/v1/charges*' => Http::response([
                'status' => 'success',
                'message' => 'Charge initiated',
                'data' => ['reference' => 'CHcuKjgnN0Dk0', 'status' => 'pending'],
            ], 200),
        ]);

        $response = $driver->initializeDirectCharge($member, 200, '2026-09', 'telebirr', '0911223344');

        $this->assertEquals('success', $response['status']);

        $contribution = Contribution::where('member_id', $member->id)->first();
        $this->assertNotNull($contribution);
        $this->assertEquals(PaymentMethod::Telebirr, $contribution->method);
        $this->assertEquals(ChapaStatus::Pending, $contribution->chapa_status);
        $this->assertEquals(200, $contribution->amount);

        Http::assertSent(function ($request) use ($contribution) {
            return str_contains($request->url(), 'charges?type=telebirr')
                && $request['amount'] === '200'
                && $request['currency'] === 'ETB'
                && $request['mobile'] === '0911223344'
                && $request['tx_ref'] === $contribution->chapa_tx_ref
                && $request['subaccounts'] === json_encode(['id' => 'sub-acct-telebirr-001']);
        });
    }

    public function test_cbebirr_direct_charge_creates_pending_contribution_and_posts_to_chapa(): void
    {
        [$idir, $member] = $this->directChargeFixture('sub-acct-cbebirr-002');

        $gatewayManager = $this->app->make(PaymentGatewayManager::class);
        $driver = $gatewayManager->driver('chapa');

        Http::fake([
            'api.chapa.co/v1/charges*' => Http::response([
                'status' => 'success',
                'message' => 'Charge initiated',
                'data' => ['reference' => 'CHcbebirr0001', 'status' => 'pending'],
            ], 200),
        ]);

        $response = $driver->initializeDirectCharge($member, 200, '2026-09', 'cbebirr', '0911223344');

        $this->assertEquals('success', $response['status']);

        $contribution = Contribution::where('member_id', $member->id)->first();
        $this->assertNotNull($contribution);
        $this->assertEquals(PaymentMethod::CBEBirr, $contribution->method);
        $this->assertEquals(ChapaStatus::Pending, $contribution->chapa_status);

        Http::assertSent(fn ($request) => str_contains($request->url(), 'charges?type=cbebirr')
            && $request['subaccounts'] === json_encode(['id' => 'sub-acct-cbebirr-002']));
    }

    public function test_direct_charge_rejected_by_chapa_marks_contribution_failed(): void
    {
        [$idir, $member] = $this->directChargeFixture();

        $gatewayManager = $this->app->make(PaymentGatewayManager::class);
        $driver = $gatewayManager->driver('chapa');

        Http::fake([
            'api.chapa.co/v1/charges*' => Http::response([
                'status' => 'failed',
                'message' => 'Invalid phone number',
                'data' => null,
            ], 400),
        ]);

        $response = $driver->initializeDirectCharge($member, 200, '2026-09', 'telebirr', '0000');

        $this->assertEquals('failed', $response['status']);

        $contribution = Contribution::where('member_id', $member->id)->first();
        $this->assertEquals(ChapaStatus::Failed, $contribution->chapa_status);
    }

    public function test_unsupported_direct_charge_type_is_rejected(): void
    {
        [$idir, $member] = $this->directChargeFixture();

        $gatewayManager = $this->app->make(PaymentGatewayManager::class);
        $driver = $gatewayManager->driver('chapa');

        $this->expectException(\InvalidArgumentException::class);
        $driver->initializeDirectCharge($member, 200, '2026-09', 'amole', '0911223344');
    }

    public function test_hosted_checkout_routes_to_subaccount_when_configured(): void
    {
        [$idir, $member] = $this->directChargeFixture('sub-acct-hosted-003');

        $gatewayManager = $this->app->make(PaymentGatewayManager::class);
        $driver = $gatewayManager->driver('chapa');

        Http::fake([
            'api.chapa.co/v1/transaction/initialize' => Http::response([
                'status' => 'success',
                'message' => 'Hosted Link',
                'data' => ['checkout_url' => 'https://checkout.chapa.co/checkout/fake-url'],
            ], 200),
        ]);

        $driver->initialize($member, 200, '2026-09');

        Http::assertSent(fn ($request) => $request->url() === 'https://api.chapa.co/v1/transaction/initialize'
            && $request['subaccounts']['id'] === 'sub-acct-hosted-003');
    }

    public function test_direct_charge_webhook_verifies_telebirr_contribution_and_updates_ledger(): void
    {
        [$idir, $member] = $this->directChargeFixture();

        $contribution = Contribution::create([
            'idir_id' => $idir->id,
            'member_id' => $member->id,
            'amount' => 200.00,
            'method' => PaymentMethod::Telebirr,
            'type' => ContributionType::Cash,
            'period_covered' => '2026-09',
            'chapa_tx_ref' => 'IDIR-DIRECT-TEST-TELEBIRR',
            'chapa_status' => ChapaStatus::Pending,
        ]);

        Http::fake([
            'api.chapa.co/v1/transaction/verify/*' => Http::response([
                'status' => 'success',
                'data' => [
                    'status' => 'success',
                    'tx_ref' => 'IDIR-DIRECT-TEST-TELEBIRR',
                    'currency' => 'ETB',
                    'amount' => 200,
                ],
            ], 200),
        ]);

        $job = new ProcessPaymentWebhookJob('chapa', 'IDIR-DIRECT-TEST-TELEBIRR');
        $job->handle(app(PaymentGatewayManager::class), app(LedgerService::class));

        $this->assertEquals(ChapaStatus::Verified, $contribution->fresh()->chapa_status);
        $this->assertEquals(200.00, $idir->settings->fresh()->fund_balance);
    }

    /**
     * @return array{0: Idir, 1: Member}
     */
    protected function directChargeFixture(?string $subaccountId = null): array
    {
        $idir = Idir::create(['name' => 'Test Idir', 'locale' => 'am']);

        IdirSetting::create([
            'idir_id' => $idir->id,
            'dues_amount' => 200.00,
            'dues_frequency' => DuesFrequency::Monthly,
            'vesting_period_days' => 90,
            'required_approvals' => 2,
            'fund_balance' => 0.00,
            'chapa_subaccount_id' => $subaccountId,
        ]);

        $member = Member::create([
            'idir_id' => $idir->id,
            'phone' => '0911000000',
            'full_name' => 'John Doe',
            'join_date' => '2023-01-01',
        ]);

        return [$idir, $member];
    }
}
