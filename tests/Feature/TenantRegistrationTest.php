<?php

namespace Tests\Feature;

use App\Filament\Pages\Tenancy\RegisterIdir;
use App\Models\Idir;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TenantRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_new_tenant_idir_through_wizard(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('committee'));

        $user = User::create([
            'name' => 'ተስፋዬ ግርማ',
            'email' => 'tesfaye@idir.et',
            'phone' => '0911776655',
            'phone_verified_at' => now(),
            'can_create_idir' => true,
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);

        Livewire::test(RegisterIdir::class)
            ->fillForm([
                'name' => 'አንድነት የሰፈር እድር',
                'membership_basis' => 'የሰፈር ነዋሪዎች',
                'region' => 'አዲስ አበባ',
                'sub_city' => 'ቂርቆስ',
                'woreda' => 'ወረዳ 05',
                'dues_amount' => 250.00,
                'dues_frequency' => 'monthly',
                'late_fee_amount' => 50.00,
                'late_fee_grace_days' => 10,
                'vesting_period_days' => 60,
                'required_approvals' => 2,
                'payout_triggers' => ['death', 'emergency'],
            ])
            ->call('register')
            ->assertHasNoFormErrors();

        // 1. Confirm row is written to idirs table
        $idir = Idir::where('name', 'አንድነት የሰፈር እድር')->first();
        $this->assertNotNull($idir, 'Idir record was not created in database');
        $this->assertEquals('ቂርቆስ', $idir->sub_city);

        // 2. Confirm row is written to idir_settings table
        $this->assertNotNull($idir->settings, 'IdirSetting record was not created');
        $this->assertEquals(250.00, $idir->settings->dues_amount);
        $this->assertEquals(60, $idir->settings->vesting_period_days);

        // 3. Confirm user is attached to idir
        $this->assertTrue($user->fresh()->canAccessTenant($idir));

        // 4. Confirm founder member record is created as Chair
        $member = $idir->members()->where('user_id', $user->id)->first();
        $this->assertNotNull($member, 'Founder member record was not created');
        $this->assertTrue($member->isChair());
    }
}
