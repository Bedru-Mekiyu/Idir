<?php

namespace Tests\Feature;

use App\Enums\ChapaStatus;
use App\Enums\PaymentMethod;
use App\Jobs\ProcessPaymentWebhookJob;
use App\Models\Contribution;
use App\Models\Idir;
use App\Models\Member;
use App\Services\Payments\PaymentGatewayManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
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
                'data' => ['checkout_url' => 'https://checkout.chapa.co/checkout/fake-url']
            ], 200),
            "api.chapa.co/v1/transaction/verify/*" => Http::response([
                'status' => 'success',
                'data' => [
                    'status' => 'success',
                    'tx_ref' => $txRef,
                    'currency' => 'ETB',
                    'amount' => 100,
                ]
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
        $job->handle($gatewayManager, $this->app->make(\App\Services\LedgerService::class));

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
                throw new \Illuminate\Http\Client\ConnectionException('Timeout');
            }
        ]);

        $this->expectException(\Illuminate\Http\Client\ConnectionException::class);
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
            'type' => \App\Enums\ContributionType::Cash,
            'period_covered' => '2023-01',
            'chapa_tx_ref' => $txRef,
            'chapa_status' => ChapaStatus::Verified,
        ]);

        Http::fake([
            "api.chapa.co/v1/transaction/verify/*" => Http::response([
                'status' => 'success',
                'data' => [
                    'status' => 'success',
                    'tx_ref' => $txRef,
                    'currency' => 'ETB',
                    'amount' => 100,
                ]
            ], 200),
        ]);

        // Attempt to process again
        $job = new ProcessPaymentWebhookJob('chapa', $txRef, []);
        $job->handle($gatewayManager, $this->app->make(\App\Services\LedgerService::class));

        // Find contribution again to make sure no duplicate was created
        $count = Contribution::where('chapa_tx_ref', $txRef)->count();
        $this->assertEquals(1, $count);
    }
}
