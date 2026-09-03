<?php

namespace Tests\Feature;

use App\Enums\DuesFrequency;
use App\Enums\MemberStatus;
use App\Enums\PaymentMethod;
use App\Models\Contribution;
use App\Models\Idir;
use App\Models\IdirSetting;
use App\Models\Member;
use App\Services\LedgerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenants_have_strictly_isolated_members_and_ledgers(): void
    {
        $ledger = app(LedgerService::class);

        // Tenant A: ሰላም እድር
        $idirA = Idir::create(['name' => 'ሰላም እድር', 'locale' => 'am']);
        IdirSetting::create([
            'idir_id' => $idirA->id,
            'dues_amount' => 200.00,
            'dues_frequency' => DuesFrequency::Monthly,
            'vesting_period_days' => 90,
            'required_approvals' => 2,
            'fund_balance' => 0.00,
        ]);
        $memberA = Member::create([
            'idir_id' => $idirA->id,
            'full_name' => 'አበበ ተሰማ (እድር ሀ)',
            'phone' => '0911000001',
            'join_date' => '2024-01-01',
            'status' => MemberStatus::Active,
        ]);

        // Tenant B: አቢሲኒያ እድር
        $idirB = Idir::create(['name' => 'አቢሲኒያ እድር', 'locale' => 'am']);
        IdirSetting::create([
            'idir_id' => $idirB->id,
            'dues_amount' => 300.00,
            'dues_frequency' => DuesFrequency::Monthly,
            'vesting_period_days' => 60,
            'required_approvals' => 1,
            'fund_balance' => 0.00,
        ]);
        $memberB = Member::create([
            'idir_id' => $idirB->id,
            'full_name' => 'ከበደ ደስታ (እድር ለ)',
            'phone' => '0911000002',
            'join_date' => '2024-01-01',
            'status' => MemberStatus::Active,
        ]);

        // Record 1000 ETB contribution for Tenant A
        $ledger->recordContribution([
            'idir_id' => $idirA->id,
            'member_id' => $memberA->id,
            'amount' => 1000.00,
            'method' => PaymentMethod::Cash,
            'period_covered' => '2026-08',
            'is_correction' => false,
        ]);

        // Record 300 ETB contribution for Tenant B
        $ledger->recordContribution([
            'idir_id' => $idirB->id,
            'member_id' => $memberB->id,
            'amount' => 300.00,
            'method' => PaymentMethod::Cash,
            'period_covered' => '2026-08',
            'is_correction' => false,
        ]);

        // Balances must be strictly isolated
        $this->assertEquals(1000.00, $idirA->settings->fresh()->fund_balance);
        $this->assertEquals(300.00, $idirB->settings->fresh()->fund_balance);

        // Members query scoping
        $this->assertCount(1, $idirA->members);
        $this->assertCount(1, $idirB->members);
        $this->assertEquals('አበበ ተሰማ (እድር ሀ)', $idirA->members->first()->full_name);
        $this->assertEquals('ከበደ ደስታ (እድር ለ)', $idirB->members->first()->full_name);
    }
}
