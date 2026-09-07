<?php

namespace Tests\Feature;

use App\Filament\Admin\Resources\AccessRequestResource;
use App\Filament\Pages\Tenancy\RegisterIdir;
use App\Models\AccessRequest;
use App\Models\Idir;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ManagerAccessAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->owner = User::create([
            'name' => 'Super Admin Platform Owner',
            'email' => 'owner@idir-platform.et',
            'phone' => '0900000000',
            'phone_verified_at' => now(),
            'password' => bcrypt('password'),
            'is_platform_owner' => true,
            'can_create_idir' => true,
        ]);
    }

    public function test_unverified_user_cannot_access_access_request_screen(): void
    {
        $user = User::create([
            'name' => 'ያልተረጋገጠ ሰው',
            'phone' => '0911001122',
            'password' => bcrypt('password'),
            'phone_verified_at' => null,
            'can_create_idir' => false,
        ]);

        $this->actingAs($user);

        $response = $this->get('/access-request');
        $response->assertRedirect('/verify-phone');
    }

    public function test_phone_verified_user_without_privilege_cannot_access_wizard(): void
    {
        $user = User::create([
            'name' => 'አዲሱ አመልካች',
            'phone' => '0911002233',
            'password' => bcrypt('password'),
            'phone_verified_at' => now(),
            'can_create_idir' => false,
        ]);

        $this->actingAs($user);

        // Attempt direct navigation to wizard URL
        $response = $this->get('/committee/new');
        $response->assertRedirect('/access-request');
        $response->assertSessionHas('warning');
    }

    public function test_user_can_submit_access_request_and_it_creates_pending_record(): void
    {
        $user = User::create([
            'name' => 'አዲሱ አመልካች',
            'phone' => '0911002233',
            'password' => bcrypt('password'),
            'phone_verified_at' => now(),
            'can_create_idir' => false,
        ]);

        $this->actingAs($user);

        $response = $this->post('/access-request', [
            'idir_name' => 'አባይ አንድነት እድር',
            'membership_basis' => 'የሰፈር ነዋሪዎች',
            'region' => 'አዲስ አበባ',
            'sub_city' => 'ቂርቆስ',
            'purpose' => 'የሠፈራችንን ማህበራዊ ግንኙነት ለማጠናከር እና የደስታና የኀዘን መረዳጃ ለማቋቋም የታሰበ እድር ነው።',
        ]);

        $response->assertRedirect('/access-request');
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('access_requests', [
            'user_id' => $user->id,
            'idir_name' => 'አባይ አንድነት እድር',
            'status' => 'pending',
        ]);
    }

    public function test_pending_access_request_shows_pending_status_on_screen(): void
    {
        $user = User::create([
            'name' => 'አዲሱ አመልካች',
            'phone' => '0911002233',
            'password' => bcrypt('password'),
            'phone_verified_at' => now(),
            'can_create_idir' => false,
        ]);

        AccessRequest::create([
            'user_id' => $user->id,
            'idir_name' => 'አባይ አንድነት እድር',
            'membership_basis' => 'የሰፈር ነዋሪዎች',
            'region' => 'አዲስ አበባ',
            'sub_city' => 'ቂርቆስ',
            'status' => 'pending',
        ]);

        $this->actingAs($user);

        $response = $this->get('/access-request');
        $response->assertStatus(200);
        $response->assertSee('ጥያቄዎ በግምገማ ላይ ነው');
        $response->assertSee('አባይ አንድነት እድር');
    }

    public function test_platform_owner_can_grant_access_request_in_admin(): void
    {
        $applicant = User::create([
            'name' => 'አብነት ታከለ',
            'phone' => '0911447788',
            'password' => bcrypt('password'),
            'phone_verified_at' => now(),
            'can_create_idir' => false,
        ]);

        $request = AccessRequest::create([
            'user_id' => $applicant->id,
            'idir_name' => 'ብርሃን እድር',
            'region' => 'ደብረ ብርሃን',
            'status' => 'pending',
        ]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($this->owner);

        Livewire::test(AccessRequestResource\Pages\ListAccessRequests::class)
            ->callTableAction('grant', $request)
            ->assertHasNoTableActionErrors();

        $request->refresh();
        $this->assertEquals('granted', $request->status);
        $this->assertEquals($this->owner->id, $request->reviewed_by);
        $this->assertNotNull($request->reviewed_at);

        // Confirm applicant's account now has the privilege
        $applicant->refresh();
        $this->assertTrue($applicant->can_create_idir);
        $this->assertTrue($applicant->canCreateIdir());
    }

    public function test_granted_user_can_access_wizard_and_create_idir_sitting_in_pending_approval(): void
    {
        $founder = User::create([
            'name' => 'አብነት ታከለ',
            'phone' => '0911447788',
            'password' => bcrypt('password'),
            'phone_verified_at' => now(),
            'can_create_idir' => true, // Granted privilege
        ]);

        Filament::setCurrentPanel(Filament::getPanel('committee'));
        $this->actingAs($founder);

        // Founder opens wizard and registers idir
        Livewire::test(RegisterIdir::class)
            ->fillForm([
                'name' => 'ብርሃን እድር',
                'membership_basis' => 'የከተማው ነዋሪዎች',
                'region' => 'አማራ',
                'sub_city' => 'ደብረ ብርሃን',
                'dues_amount' => 200.00,
                'dues_frequency' => 'monthly',
                'vesting_period_days' => 90,
                'required_approvals' => 2,
                'payout_triggers' => ['death', 'emergency'],
            ])
            ->call('register')
            ->assertHasNoFormErrors();

        $idir = Idir::where('name', 'ብርሃን እድር')->first();
        $this->assertNotNull($idir);

        // Confirm the second distinct gate: the idir sits in pending_approval!
        $this->assertEquals('pending_approval', $idir->status);
        $this->assertTrue($idir->isPendingApproval());
    }

    public function test_platform_owner_can_deny_access_request_with_reason(): void
    {
        $applicant = User::create([
            'name' => 'ተክሌ ወልደሃይማኖት',
            'phone' => '0922334455',
            'password' => bcrypt('password'),
            'phone_verified_at' => now(),
            'can_create_idir' => false,
        ]);

        $request = AccessRequest::create([
            'user_id' => $applicant->id,
            'idir_name' => 'ሰላም እድር',
            'status' => 'pending',
        ]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs($this->owner);

        Livewire::test(AccessRequestResource\Pages\ListAccessRequests::class)
            ->callTableAction('deny', $request, [
                'denial_reason' => 'የቀረበው መረጃ እና የአካባቢው አድራሻ ያልተሟላ በመሆኑ።',
            ])
            ->assertHasNoTableActionErrors();

        $request->refresh();
        $this->assertEquals('denied', $request->status);
        $this->assertEquals('የቀረበው መረጃ እና የአካባቢው አድራሻ ያልተሟላ በመሆኑ።', $request->denial_reason);
        $this->assertEquals($this->owner->id, $request->reviewed_by);

        // Applicant account remains unauthorized
        $applicant->refresh();
        $this->assertFalse($applicant->can_create_idir);
        $this->assertFalse($applicant->canCreateIdir());
    }

    public function test_denied_user_sees_denial_reason_and_cannot_access_wizard(): void
    {
        $applicant = User::create([
            'name' => 'ተክሌ ወልደሃይማኖት',
            'phone' => '0922334455',
            'password' => bcrypt('password'),
            'phone_verified_at' => now(),
            'can_create_idir' => false,
        ]);

        AccessRequest::create([
            'user_id' => $applicant->id,
            'idir_name' => 'ሰላም እድር',
            'status' => 'denied',
            'denial_reason' => 'የቀረበው መረጃ እና የአካባቢው አድራሻ ያልተሟላ በመሆኑ።',
            'reviewed_at' => now(),
        ]);

        $this->actingAs($applicant);

        // Sees denial on status page
        $response = $this->get('/access-request');
        $response->assertStatus(200);
        $response->assertSee('ጥያቄዎ ውድቅ ተደርጓል');
        $response->assertSee('የቀረበው መረጃ እና የአካባቢው አድራሻ ያልተሟላ በመሆኑ።');

        // Cannot reach wizard
        $wizardResponse = $this->get('/committee/new');
        $wizardResponse->assertRedirect('/access-request');
    }

    public function test_denied_user_can_submit_new_access_request(): void
    {
        $applicant = User::create([
            'name' => 'ተክሌ ወልደሃይማኖት',
            'phone' => '0922334455',
            'password' => bcrypt('password'),
            'phone_verified_at' => now(),
            'can_create_idir' => false,
        ]);

        AccessRequest::create([
            'user_id' => $applicant->id,
            'idir_name' => 'ሰላም እድር',
            'status' => 'denied',
            'denial_reason' => 'የቀረበው መረጃ ያልተሟላ በመሆኑ።',
            'reviewed_at' => now(),
        ]);

        $this->actingAs($applicant);

        // Can access reapply form
        $formResponse = $this->get('/access-request?reapply=1');
        $formResponse->assertStatus(200);
        $formResponse->assertSee('የማኔጀርነት ፈቃድ ማመልከቻ');

        // Submits new request
        $postResponse = $this->post('/access-request', [
            'idir_name' => 'ሰላም እድር የተስተካከለ',
            'membership_basis' => 'የሰፈር ነዋሪዎች',
            'region' => 'አዲስ አበባ',
            'sub_city' => 'ቦሌ',
            'purpose' => 'የተሟላ አድራሻና ሰነድ ተካቷል።',
        ]);

        $postResponse->assertRedirect('/access-request');

        $this->assertDatabaseHas('access_requests', [
            'user_id' => $applicant->id,
            'idir_name' => 'ሰላም እድር የተስተካከለ',
            'status' => 'pending',
        ]);
    }

    public function test_existing_active_idirs_and_chairs_remain_unaffected(): void
    {
        $chair = User::create([
            'name' => 'አበበ ተሰማ ደስታ',
            'email' => 'chair@idir.et',
            'phone' => '0911223344',
            'phone_verified_at' => now(),
            'can_create_idir' => true,
            'password' => bcrypt('password'),
        ]);

        $idir = Idir::create([
            'name' => 'ፋሲለደስ እድር',
            'status' => 'active',
            'locale' => 'am',
        ]);

        $idir->users()->attach($chair);

        $this->actingAs($chair);

        Filament::setCurrentPanel(Filament::getPanel('committee'));

        $this->assertTrue($chair->canAccessTenant($idir));
        $this->assertTrue($chair->idirs()->exists());
    }
}
