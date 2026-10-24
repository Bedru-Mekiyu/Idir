<?php

namespace Tests\Feature\Payments;

use App\Enums\ChapaStatus;
use App\Enums\PaymentMethod;
use App\Models\Contribution;
use App\Models\Idir;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TelebirrWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_telebirr_webhook_verifies_contribution_on_success()
    {
        $user = User::create(['name' => 'Test', 'email' => 'tb1@example.com', 'password' => 'pwd', 'phone' => '0911000000']);
        $idir = Idir::create(['name' => 'Test Idir', 'locale' => 'am']);
        $member = Member::create(['idir_id' => $idir->id, 'user_id' => $user->id, 'full_name' => 'Test', 'phone' => '0911000000', 'join_date' => '2023-01-01', 'status' => 'active']);

        $contribution = Contribution::create([
            'idir_id' => $idir->id,
            'member_id' => $member->id,
            'method' => PaymentMethod::Telebirr,
            'chapa_status' => ChapaStatus::Pending,
            'chapa_tx_ref' => 'IDIR-TB-123',
            'amount' => 100,
            'period_covered' => '2023-01',
        ]);

        $response = $this->postJson('/api/telebirr/webhook', [
            'merch_order_id' => 'IDIR-TB-123',
            'trade_status' => 'Payment successful',
        ]);

        $response->assertOk();
        $this->assertEquals(ChapaStatus::Verified, $contribution->fresh()->chapa_status);
    }

    public function test_telebirr_webhook_ignores_unknown_tx_ref()
    {
        $response = $this->postJson('/api/telebirr/webhook', [
            'merch_order_id' => 'UNKNOWN-123',
            'trade_status' => 'Payment successful',
        ]);

        $response->assertOk();
    }

    public function test_telebirr_webhook_marks_failed_on_unsuccessful_payment()
    {
        $user = User::create(['name' => 'Test', 'email' => 'tb2@example.com', 'password' => 'pwd', 'phone' => '0911000000']);
        $idir = Idir::create(['name' => 'Test Idir', 'locale' => 'am']);
        $member = Member::create(['idir_id' => $idir->id, 'user_id' => $user->id, 'full_name' => 'Test', 'phone' => '0911000000', 'join_date' => '2023-01-01', 'status' => 'active']);

        $contribution = Contribution::create([
            'idir_id' => $idir->id,
            'member_id' => $member->id,
            'method' => PaymentMethod::Telebirr,
            'chapa_status' => ChapaStatus::Pending,
            'chapa_tx_ref' => 'IDIR-TB-FAIL',
            'amount' => 100,
            'period_covered' => '2023-01',
        ]);

        $response = $this->postJson('/api/telebirr/webhook', [
            'merch_order_id' => 'IDIR-TB-FAIL',
            'trade_status' => 'Failed',
        ]);

        $response->assertOk();
        $this->assertEquals(ChapaStatus::Failed, $contribution->fresh()->chapa_status);
    }
}
