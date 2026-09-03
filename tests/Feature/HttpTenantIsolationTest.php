<?php

namespace Tests\Feature;

use App\Enums\DuesFrequency;
use App\Enums\MemberStatus;
use App\Models\Idir;
use App\Models\IdirSetting;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HttpTenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_access_another_tenant_committee_panel_at_http_layer(): void
    {
        // Tenant A
        $idirA = Idir::create(['name' => 'ሰላም እድር', 'locale' => 'am']);
        IdirSetting::create([
            'idir_id' => $idirA->id,
            'dues_amount' => 200.00,
            'dues_frequency' => DuesFrequency::Monthly,
            'vesting_period_days' => 90,
            'required_approvals' => 2,
            'fund_balance' => 0.00,
        ]);
        $userA = User::create([
            'name' => 'አበበ ተሰማ',
            'email' => 'userA@idir.et',
            'phone' => '0911000001',
            'password' => bcrypt('password'),
        ]);
        $idirA->users()->attach($userA);

        // Tenant B
        $idirB = Idir::create(['name' => 'አቢሲኒያ እድር', 'locale' => 'am']);
        IdirSetting::create([
            'idir_id' => $idirB->id,
            'dues_amount' => 300.00,
            'dues_frequency' => DuesFrequency::Monthly,
            'vesting_period_days' => 60,
            'required_approvals' => 1,
            'fund_balance' => 0.00,
        ]);
        $userB = User::create([
            'name' => 'ከበደ ደስታ',
            'email' => 'userB@idir.et',
            'phone' => '0911000002',
            'password' => bcrypt('password'),
        ]);
        $idirB->users()->attach($userB);

        // User A accessing Tenant A panel -> Allowed (200 OK)
        $responseA = $this->actingAs($userA)->get("/committee/{$idirA->id}");
        $responseA->assertStatus(200);

        // User A attempting to access Tenant B panel by guessing ID in URL -> REJECTED (404/403)
        $responseB = $this->actingAs($userA)->get("/committee/{$idirB->id}");
        $this->assertTrue(in_array($responseB->getStatusCode(), [403, 404]));
    }

    public function test_api_member_profile_is_isolated_to_authenticated_user(): void
    {
        $idirA = Idir::create(['name' => 'ሰላም እድር', 'locale' => 'am']);
        $userA = User::create([
            'name' => 'አበበ ተሰማ',
            'email' => 'userA@idir.et',
            'phone' => '0911000001',
            'password' => bcrypt('password'),
        ]);
        $memberA = Member::create([
            'idir_id' => $idirA->id,
            'user_id' => $userA->id,
            'full_name' => 'አበበ ተሰማ',
            'phone' => '0911000001',
            'join_date' => '2024-01-01',
            'status' => MemberStatus::Active,
        ]);

        $token = $userA->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/member/profile');

        $response->assertStatus(200);
        $response->assertJsonPath('id', $memberA->id);
        $response->assertJsonPath('full_name', 'አበበ ተሰማ');
    }
}
