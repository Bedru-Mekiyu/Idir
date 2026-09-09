<?php

namespace Tests\Feature;

use App\Enums\CommitteeRole;
use App\Enums\MemberStatus;
use App\Filament\Pages\Tenancy\EditIdirProfile;
use App\Models\Idir;
use App\Models\IdirSetting;
use App\Models\Member;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EditIdirProfileTest extends TestCase
{
    use RefreshDatabase;

    protected User $chair;

    protected Idir $idir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->chair = User::create([
            'name' => 'አበበ በቀለ',
            'phone' => '0911223344',
            'phone_verified_at' => now(),
            'password' => bcrypt('password'),
        ]);

        $this->idir = Idir::create([
            'name' => 'አንድነት የሰፈር እድር',
            'locale' => 'am',
            'status' => 'active',
        ]);

        $this->idir->users()->attach($this->chair);
        $this->idir->settings()->create([
            'dues_amount' => 200.00,
            'dues_frequency' => 'monthly',
            'fund_balance' => 0.00,
        ]);

        Member::create([
            'idir_id' => $this->idir->id,
            'user_id' => $this->chair->id,
            'full_name' => $this->chair->name,
            'phone' => $this->chair->phone,
            'join_date' => now()->toDateString(),
            'status' => MemberStatus::Active,
            'committee_role' => CommitteeRole::Chair,
        ]);

        Filament::setCurrentPanel(Filament::getPanel('committee'));
        $this->actingAs($this->chair);
        Filament::setTenant($this->idir);
    }

    public function test_chair_can_save_chapa_subaccount_id_from_settings_page(): void
    {
        Livewire::test(EditIdirProfile::class)
            ->fillForm([
                'name' => 'አንድነት የሰፈር እድር',
                'chapa_subaccount_id' => 'sub-acct-idir-001',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertEquals('sub-acct-idir-001', $this->idir->settings->fresh()->chapa_subaccount_id);
    }

    public function test_existing_subaccount_id_is_prefilled_on_settings_page(): void
    {
        $this->idir->settings()->update(['chapa_subaccount_id' => 'sub-acct-existing-999']);

        Livewire::test(EditIdirProfile::class)
            ->assertFormSet([
                'chapa_subaccount_id' => 'sub-acct-existing-999',
            ]);
    }

    public function test_chair_can_clear_chapa_subaccount_id(): void
    {
        $this->idir->settings()->update(['chapa_subaccount_id' => 'sub-acct-to-clear']);

        Livewire::test(EditIdirProfile::class)
            ->fillForm([
                'name' => 'አንድነት የሰፈር እድር',
                'chapa_subaccount_id' => null,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertNull($this->idir->settings->fresh()->chapa_subaccount_id);
    }

    public function test_other_profile_fields_still_save_alongside_subaccount_id(): void
    {
        Livewire::test(EditIdirProfile::class)
            ->fillForm([
                'name' => 'የተሻሻለ እድር ስም',
                'region' => 'ኦሮሚያ',
                'chapa_subaccount_id' => 'sub-acct-777',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertEquals('የተሻሻለ እድር ስም', $this->idir->fresh()->name);
        $this->assertEquals('ኦሮሚያ', $this->idir->fresh()->region);
        $this->assertEquals('sub-acct-777', $this->idir->settings->fresh()->chapa_subaccount_id);
    }

    public function test_subaccount_id_created_even_when_settings_row_missing(): void
    {
        $this->idir->settings()->delete();

        Livewire::test(EditIdirProfile::class)
            ->fillForm([
                'name' => 'አንድነት የሰፈር እድር',
                'chapa_subaccount_id' => 'sub-acct-fresh',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $setting = IdirSetting::where('idir_id', $this->idir->id)->first();
        $this->assertNotNull($setting);
        $this->assertEquals('sub-acct-fresh', $setting->chapa_subaccount_id);
    }
}
