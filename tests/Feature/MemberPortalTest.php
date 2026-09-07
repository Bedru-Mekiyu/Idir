<?php

namespace Tests\Feature;

use App\Enums\ClaimStatus;
use App\Enums\DuesFrequency;
use App\Enums\MemberStatus;
use App\Models\Idir;
use App\Models\IdirSetting;
use App\Models\Member;
use App\Models\PayoutTriggerType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MemberPortalTest extends TestCase
{
    use RefreshDatabase;

    protected Idir $idir;

    protected User $user;

    protected Member $member;

    protected PayoutTriggerType $trigger;

    protected function setUp(): void
    {
        parent::setUp();

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

        $this->trigger = PayoutTriggerType::create([
            'idir_id' => $this->idir->id,
            'name' => 'death',
            'label_am' => 'ሞት',
            'default_payout_amount' => 8000.00,
            'is_active' => true,
        ]);
    }

    public function test_member_can_view_dashboard(): void
    {
        $response = $this->actingAs($this->user)->get('/member');

        $response->assertStatus(200);
        $response->assertSee('ሙሉጌታ ኃይለ ማርያም');
        $response->assertSee('ለክፍያ ብቁ');
    }

    public function test_member_can_file_claim_with_document_upload(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('death_certificate.pdf', 500, 'application/pdf');

        $response = $this->actingAs($this->user)->post('/member/claims', [
            'payout_trigger_type_id' => $this->trigger->id,
            'requested_amount' => 8000.00,
            'description' => 'የቀብር እርዳታ ጥያቄ (የቀበሌ ማረጋገጫ ተያይዟል)',
            'document' => $file,
        ]);

        $response->assertRedirect('/member');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('claims', [
            'idir_id' => $this->idir->id,
            'member_id' => $this->member->id,
            'payout_trigger_type_id' => $this->trigger->id,
            'status' => ClaimStatus::Pending->value,
            'requested_amount' => 8000.00,
        ]);
    }

    public function test_member_cannot_file_claim_with_trigger_from_another_idir(): void
    {
        $otherIdir = Idir::create(['name' => 'ሌላ እድር', 'locale' => 'am']);

        $foreignTrigger = PayoutTriggerType::create([
            'idir_id' => $otherIdir->id,
            'name' => 'death',
            'label_am' => 'ሞት',
            'default_payout_amount' => 9999.00,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->user)->post('/member/claims', [
            'payout_trigger_type_id' => $foreignTrigger->id,
            'requested_amount' => 8000.00,
            'description' => 'የውጭ እድር ትሪገር ለመጠቀም የተደረገ ሙከራ',
        ]);

        $response->assertSessionHasErrors('payout_trigger_type_id');
        $this->assertDatabaseCount('claims', 0);
    }
}
