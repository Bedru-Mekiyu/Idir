<?php

namespace Database\Seeders;

use App\Enums\ApprovalDecision;
use App\Enums\ChapaStatus;
use App\Enums\ClaimStatus;
use App\Enums\CommitteeRole;
use App\Enums\ContributionType;
use App\Enums\DocumentCategory;
use App\Enums\DuesFrequency;
use App\Enums\MeetingType;
use App\Enums\MemberStatus;
use App\Enums\NotificationChannel;
use App\Enums\NotificationStatus;
use App\Enums\NotificationType;
use App\Enums\PaymentMethod;
use App\Models\Claim;
use App\Models\ClaimApproval;
use App\Models\Contribution;
use App\Models\ContributionRule;
use App\Models\Disbursement;
use App\Models\Document;
use App\Models\Idir;
use App\Models\IdirSetting;
use App\Models\Meeting;
use App\Models\Member;
use App\Models\NotificationEvent;
use App\Models\NotificationPreference;
use App\Models\PayoutTriggerType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EthiopianSampleDataSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Create Platform Owner (Super Admin)
        $ownerUser = User::create([
            'name' => 'የፕላትፎርም ባለቤት (Platform Owner)',
            'email' => 'owner@idir-platform.et',
            'phone' => '0900000000',
            'phone_verified_at' => now(),
            'password' => Hash::make('password'),
            'is_platform_owner' => true,
            'can_create_idir' => true,
        ]);

        // 1. Create Committee Users
        $chairUser = User::create([
            'name' => 'አበበ ተሰማ ደስታ',
            'email' => 'chair@idir.et',
            'phone' => '0911223344',
            'phone_verified_at' => now(),
            'password' => Hash::make('password'),
            'is_platform_owner' => false,
            'can_create_idir' => true,
        ]);

        $treasurerUser = User::create([
            'name' => 'ከበደ ደስታ ወርቁ',
            'email' => 'treasurer@idir.et',
            'phone' => '0911334455',
            'phone_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);

        $secretaryUser = User::create([
            'name' => 'ማርያም ገብረ ሚካኤል',
            'email' => 'secretary@idir.et',
            'phone' => '0911445566',
            'phone_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);

        // 2. Create Tenant Idir 1: Neighborhood Association
        $idir1 = Idir::create([
            'name' => 'ሰላም የሰፈር እድር',
            'membership_basis' => 'የሰፈር ነዋሪዎች ማህበር',
            'region' => 'አዲስ አበባ',
            'sub_city' => 'ቦሌ',
            'woreda' => 'ወረዳ 03',
            'locale' => 'am',
        ]);

        $idir1->users()->attach([$chairUser->id, $treasurerUser->id, $secretaryUser->id]);

        IdirSetting::create([
            'idir_id' => $idir1->id,
            'dues_amount' => 200.00,
            'dues_frequency' => DuesFrequency::Monthly,
            'late_fee_amount' => 50.00,
            'late_fee_grace_days' => 7,
            'vesting_period_days' => 90,
            'required_approvals' => 2,
            'enabled_payout_triggers' => ['death', 'wedding', 'emergency'],
            'fund_balance' => 0.00,
        ]);

        ContributionRule::create([
            'idir_id' => $idir1->id,
            'category_name' => 'መደበኛ አባል',
            'amount' => 200.00,
            'frequency' => 'monthly',
            'is_default' => true,
        ]);

        ContributionRule::create([
            'idir_id' => $idir1->id,
            'category_name' => 'አረጋውያን',
            'amount' => 100.00,
            'frequency' => 'monthly',
            'is_default' => false,
        ]);

        $deathTrigger1 = PayoutTriggerType::create([
            'idir_id' => $idir1->id,
            'name' => 'death',
            'label_am' => 'ሞት',
            'default_payout_amount' => 10000.00,
            'is_active' => true,
        ]);

        $weddingTrigger1 = PayoutTriggerType::create([
            'idir_id' => $idir1->id,
            'name' => 'wedding',
            'label_am' => 'ሰርግ',
            'default_payout_amount' => 3000.00,
            'is_active' => true,
        ]);

        $emergencyTrigger1 = PayoutTriggerType::create([
            'idir_id' => $idir1->id,
            'name' => 'emergency',
            'label_am' => 'ድንገተኛ አደጋ',
            'default_payout_amount' => 5000.00,
            'is_active' => true,
        ]);

        // Seed Notification Preferences for Idir 1
        $notificationTypes = [
            'due_reminder' => 'ውድ :member_name፣ የ:period ወር መዋጮ :amount ብር እስከ ወሩ መጨረሻ እንዲከፍሉ እናስታውሳለን። :idir_name',
            'late_warning' => 'ማስጠንቀቂያ፦ ውድ :member_name፣ የ:period ወር መዋጮ ስላልከፈሉ ቅጣት ከመጣሉ በፊት በአስቸኳይ ይክፈሉ። :idir_name',
            'payment_confirmation' => 'ክፍያ ተረጋግጧል፦ ውድ :member_name፣ ለ:period ወር የተከፈለው :amount ብር ገቢ ሆኗል። እናመሰግናለን! :idir_name',
            'claim_filed' => 'የክፍያ ጥያቄ፦ ለአባል :member_name በ:trigger ምክንያት የቀረበው ጥያቄ ለኮሚቴ ግምገማ ቀርቧል።',
            'claim_approved' => 'ጥያቄ ፀድቋል፦ ለአባል :member_name የቀረበው የ:amount ብር ክፍያ ጥያቄ በኮሚቴው ፀድቋል።',
            'claim_rejected' => 'ውድ :member_name፣ ያቀረቡት የክፍያ ጥያቄ በኮሚቴው ውድቅ ተደርጓል። ምክንያት፦ :reason',
            'disbursement_made' => 'ክፍያ ተፈጽሟል፦ ለአባል :member_name የ:amount ብር ክፍያ ተፈጽሟል።',
            'exclusion_warning' => 'አስቸኳይ ማስጠንቀቂያ፦ ውድ :member_name፣ ያለብዎትን ያልተከፈለ መዋጮ ካላጠናቀቁ ከእድር ሊወገዱ እንደሚችሉ እናሳውቃለን። :idir_name',
            'general_announcement' => 'አጠቃላይ ማስታወቂያ፦ :message - :idir_name',
            'new_member_welcome' => 'እንኳን ደህና መጡ፦ ውድ :member_name፣ ወደ :idir_name በደህና መጡ! ወርሃዊ መዋጮ :amount ብር ነው።',
        ];

        foreach ($notificationTypes as $type => $templateAm) {
            NotificationPreference::create([
                'idir_id' => $idir1->id,
                'event_type' => NotificationType::from($type),
                'sms_enabled' => true,
                'telegram_enabled' => false,
                'in_app_enabled' => true,
                'template_am' => $templateAm,
            ]);
        }

        // 3. Create Committee Members for Idir 1
        $chairMember = Member::create([
            'idir_id' => $idir1->id,
            'user_id' => $chairUser->id,
            'full_name' => $chairUser->name,
            'phone' => $chairUser->phone,
            'fayda_id' => 'FIN-ET-8921-3841-0912',
            'fayda_verified' => true,
            'join_date' => '2024-01-01',
            'status' => MemberStatus::Active,
            'committee_role' => CommitteeRole::Chair,
        ]);

        $treasurerMember = Member::create([
            'idir_id' => $idir1->id,
            'user_id' => $treasurerUser->id,
            'full_name' => $treasurerUser->name,
            'phone' => $treasurerUser->phone,
            'fayda_id' => 'FIN-ET-7832-1920-4821',
            'fayda_verified' => true,
            'join_date' => '2024-01-01',
            'status' => MemberStatus::Active,
            'committee_role' => CommitteeRole::Treasurer,
        ]);

        $secretaryMember = Member::create([
            'idir_id' => $idir1->id,
            'user_id' => $secretaryUser->id,
            'full_name' => $secretaryUser->name,
            'phone' => $secretaryUser->phone,
            'fayda_id' => null,
            'fayda_verified' => false,
            'join_date' => '2024-01-01',
            'status' => MemberStatus::Active,
            'committee_role' => CommitteeRole::Secretary,
        ]);

        // 4. Create Realistic Ethiopian Members for Idir 1
        $ethiopianMembersData = [
            ['ታደለ ወርቁ አየለ', '0911778899', '2024-02-15', MemberStatus::Active, true, 'FIN-ET-1122-3344-5566'],
            ['አልማዝ ኃይሌ በርሄ', '0912345678', '2024-03-01', MemberStatus::Active, true, 'FIN-ET-2233-4455-6677'],
            ['ዘውዲቱ በቀለ ተፈራ', '0920112233', '2024-03-10', MemberStatus::Active, false, null],
            ['ሙሉጌታ ኃይለ ማርያም', '0922445566', '2024-04-01', MemberStatus::Active, true, 'FIN-ET-3344-5566-7788'],
            ['ብርሃኑ አስፋው ዘለቀ', '0933112233', '2024-04-15', MemberStatus::InArrears, false, null],
            ['ትዕግስት ታደሰ ካሳ', '0944223344', '2024-05-01', MemberStatus::Active, true, 'FIN-ET-4455-6677-8899'],
            ['ዮሐንስ ፍስሐ ኪዳኔ', '0911889900', '2024-05-20', MemberStatus::Active, false, null],
            ['ሰላማዊት አበራ ዳዲ', '0912990011', '2024-06-01', MemberStatus::Active, true, 'FIN-ET-5566-7788-9900'],
            ['ጌታቸው መንግስቱ ንጉሴ', '0913001122', '2024-06-15', MemberStatus::InArrears, false, null],
            ['ሮዛ ከበደ መኮንን', '0914112233', '2024-07-01', MemberStatus::Active, false, null],
            ['ዳዊት ታከለ ለማ', '0915223344', '2024-07-10', MemberStatus::Active, true, 'FIN-ET-6677-8899-0011'],
            ['ፋንቱ ገብረ ኪዳን', '0916334455', '2024-01-10', MemberStatus::Excluded, false, null],
        ];

        $regularUser = User::create([
            'name' => 'ሙሉጌታ ኃይለ ማርያም',
            'email' => 'member@idir.et',
            'phone' => '0922445566',
            'phone_verified_at' => now(),
            'password' => Hash::make('password'),
        ]);

        $createdMembers = [];
        foreach ($ethiopianMembersData as $mData) {
            $userId = ($mData[1] === '0922445566') ? $regularUser->id : null;
            $m = Member::create([
                'idir_id' => $idir1->id,
                'user_id' => $userId,
                'full_name' => $mData[0],
                'phone' => $mData[1],
                'join_date' => $mData[2],
                'status' => $mData[3],
                'fayda_verified' => $mData[4],
                'fayda_id' => $mData[5],
                'exclusion_reason' => $mData[3] === MemberStatus::Excluded ? 'ለ6 ወራት ያህል መዋጮ ባለመክፈል እና ማስጠንቀቂያ ባለመቀበል' : null,
                'excluded_at' => $mData[3] === MemberStatus::Excluded ? '2025-06-01 10:00:00' : null,
                'exclusion_warning_sent_at' => $mData[3] === MemberStatus::Excluded ? '2025-05-01 09:30:00' : null,
            ]);
            $createdMembers[] = $m;
        }

        // 5. Seed Realistic Contributions
        $periods = ['2026-05', '2026-06', '2026-07', '2026-08'];
        $totalContributions = 0;

        foreach ($createdMembers as $idx => $member) {
            if ($member->status === MemberStatus::Excluded) {
                continue;
            }

            $periodsToPay = ($member->status === MemberStatus::InArrears) ? ['2026-05', '2026-06'] : $periods;

            foreach ($periodsToPay as $p) {
                $isChapa = ($idx % 3 === 0);
                $c = Contribution::create([
                    'idir_id' => $idir1->id,
                    'member_id' => $member->id,
                    'recorded_by_member_id' => $isChapa ? null : $treasurerMember->id,
                    'amount' => 200.00,
                    'method' => $isChapa ? PaymentMethod::Chapa : PaymentMethod::Cash,
                    'type' => ContributionType::Cash,
                    'period_covered' => $p,
                    'chapa_tx_ref' => $isChapa ? 'IDIR-'.$idir1->id.'-'.$member->id.'-'.Str::uuid() : null,
                    'chapa_status' => $isChapa ? ChapaStatus::Verified : null,
                    'notes' => $isChapa ? 'በቴሌብር በቻፓ የተከፈለ' : 'በጥሬ ገንዘብ የተከፈለ',
                    'is_correction' => false,
                ]);
                $totalContributions += 200.00;
            }
        }

        // Add a correction sample
        $sampleOriginal = Contribution::where('idir_id', $idir1->id)->first();
        if ($sampleOriginal) {
            Contribution::create([
                'idir_id' => $idir1->id,
                'member_id' => $sampleOriginal->member_id,
                'recorded_by_member_id' => $treasurerMember->id,
                'amount' => -200.00,
                'method' => PaymentMethod::Cash,
                'type' => ContributionType::Cash,
                'period_covered' => $sampleOriginal->period_covered,
                'notes' => 'ስህተት የተመዘገበ ክፍያ ማስተካከያ (ደረሰኝ #001)',
                'is_correction' => true,
                'corrected_contribution_id' => $sampleOriginal->id,
            ]);
            $totalContributions -= 200.00;
        }

        // 6. Seed Claims & Approvals
        // Claim 1: Fully approved & paid death claim
        $vestedMember = $createdMembers[0]; // Joined Feb 2024
        $claim1 = Claim::create([
            'idir_id' => $idir1->id,
            'member_id' => $vestedMember->id,
            'payout_trigger_type_id' => $deathTrigger1->id,
            'description' => 'የአባታቸው የቀብር ስነ-ስርዓት ድጋፍ ጥያቄ (የቀበሌ ማረጋገጫ ተያይዟል)',
            'requested_amount' => 10000.00,
            'status' => ClaimStatus::Paid,
        ]);

        ClaimApproval::create([
            'claim_id' => $claim1->id,
            'approver_member_id' => $chairMember->id,
            'decision' => ApprovalDecision::Approved,
            'approved_amount' => 10000.00,
            'remarks' => 'ሰነዱ ትክክለኛ ነው፣ ክፍያው እንዲፈጸም አረጋግጣለሁ።',
            'decided_at' => now()->subDays(5),
        ]);

        ClaimApproval::create([
            'claim_id' => $claim1->id,
            'approver_member_id' => $treasurerMember->id,
            'decision' => ApprovalDecision::Approved,
            'approved_amount' => 10000.00,
            'remarks' => 'በደንቡ መሰረት የሞት ካሳ መጠን ተረጋግጧል።',
            'decided_at' => now()->subDays(4),
        ]);

        $disbursement1 = Disbursement::create([
            'idir_id' => $idir1->id,
            'claim_id' => $claim1->id,
            'member_id' => $vestedMember->id,
            'amount' => 10000.00,
            'method' => 'cash',
            'recorded_by_member_id' => $treasurerMember->id,
            'notes' => 'የቀብር ድጋፍ በጥሬ ገንዘብ ተፈጽሟል። ደረሰኝ ቁጥር #984',
        ]);
        $totalDisbursements = 10000.00;

        // Claim 2: Pending review wedding claim
        $vestedMember2 = $createdMembers[1];
        $claim2 = Claim::create([
            'idir_id' => $idir1->id,
            'member_id' => $vestedMember2->id,
            'payout_trigger_type_id' => $weddingTrigger1->id,
            'description' => 'የልጃቸው የሰርግ ድግስ ድጋፍ ጥያቄ',
            'requested_amount' => 3000.00,
            'status' => ClaimStatus::UnderReview,
        ]);

        ClaimApproval::create([
            'claim_id' => $claim2->id,
            'approver_member_id' => $chairMember->id,
            'decision' => ApprovalDecision::Approved,
            'approved_amount' => 3000.00,
            'remarks' => 'የሰርግ ጥሪ ወረቀት ቀርቧል፣ ተቀባይነት አግኝቷል።',
            'decided_at' => now()->subDay(),
        ]);

        // 7. Recalculate Fund Balance for Idir 1
        $idir1->settings->update([
            'fund_balance' => $totalContributions - $totalDisbursements,
        ]);

        // 8. Seed Meetings
        Meeting::create([
            'idir_id' => $idir1->id,
            'type' => MeetingType::GeneralAssembly,
            'meeting_date' => '2026-07-05',
            'minutes' => 'የ2018 ዓ.ም የሩብ ዓመት ጠቅላላ ጉባኤ ተካሂዷል። በስብሰባው ላይ የገንዘብ ሪፖርት ቀርቦ በሙሉ ድምፅ ፀድቋል።',
            'recorded_by_member_id' => $secretaryMember->id,
        ]);

        Meeting::create([
            'idir_id' => $idir1->id,
            'type' => MeetingType::Executive,
            'meeting_date' => '2026-08-01',
            'minutes' => 'የስራ አስፈጻሚ ኮሚቴ ስብሰባ፦ በዘገዩ አባላት ላይ የተወሰደው እርምጃ እና የዲጂታል ክፍያ አጠቃቀም ተገምግሟል።',
            'recorded_by_member_id' => $secretaryMember->id,
        ]);

        // 9. Seed Documents
        Document::create([
            'idir_id' => $idir1->id,
            'title' => 'የሰላም የሰፈር እድር መተዳደሪያ ደንብ (2016)',
            'file_path' => 'documents/bylaws_selam_idir.pdf',
            'category' => DocumentCategory::Bylaws,
            'uploaded_by_member_id' => $chairMember->id,
        ]);

        // 10. Seed Notification Events
        NotificationEvent::create([
            'idir_id' => $idir1->id,
            'member_id' => $vestedMember->id,
            'type' => NotificationType::DisbursementMade,
            'channel' => NotificationChannel::Sms,
            'status' => NotificationStatus::Sent,
            'message_content' => 'ክፍያ ተፈጽሟል፦ ለአባል ታደለ ወርቁ አየለ የ10000.00 ብር ክፍያ ተፈጽሟል።',
            'sent_at' => now()->subDays(4),
        ]);

        // 11. Create Tenant Idir 2: Workplace Association
        $idir2 = Idir::create([
            'name' => 'አቢሲኒያ የሰራተኞች እድር',
            'membership_basis' => 'የኩባንያ ሰራተኞች ማህበር',
            'region' => 'አዲስ አበባ',
            'sub_city' => 'ቂርቆስ',
            'woreda' => 'ወረዳ 02',
            'locale' => 'am',
        ]);

        $idir2->users()->attach([$chairUser->id]);

        IdirSetting::create([
            'idir_id' => $idir2->id,
            'dues_amount' => 300.00,
            'dues_frequency' => DuesFrequency::Monthly,
            'late_fee_amount' => 100.00,
            'late_fee_grace_days' => 5,
            'vesting_period_days' => 60,
            'required_approvals' => 1,
            'enabled_payout_triggers' => ['death', 'emergency'],
            'fund_balance' => 0.00,
        ]);

        ContributionRule::create([
            'idir_id' => $idir2->id,
            'category_name' => 'ሙሉ ሰራተኛ',
            'amount' => 300.00,
            'frequency' => 'monthly',
            'is_default' => true,
        ]);

        PayoutTriggerType::create([
            'idir_id' => $idir2->id,
            'name' => 'death',
            'label_am' => 'ሞት',
            'default_payout_amount' => 15000.00,
            'is_active' => true,
        ]);

        Member::create([
            'idir_id' => $idir2->id,
            'user_id' => $chairUser->id,
            'full_name' => $chairUser->name,
            'phone' => $chairUser->phone,
            'join_date' => '2024-01-01',
            'status' => MemberStatus::Active,
            'committee_role' => CommitteeRole::Chair,
        ]);
    }
}
