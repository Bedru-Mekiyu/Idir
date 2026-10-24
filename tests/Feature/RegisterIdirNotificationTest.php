<?php

namespace Tests\Feature;

use App\Enums\NotificationType;
use App\Filament\Pages\Tenancy\RegisterIdir;
use App\Jobs\SendNotificationJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class RegisterIdirNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_member_welcome_dispatched_on_register_idir()
    {
        Queue::fake();

        $user = User::factory()->create([
            'phone_verified_at' => now(),
            'can_create_idir' => true,
        ]);

        $this->actingAs($user);

        Livewire::test(RegisterIdir::class)
            ->fillForm([
                'name' => 'New Idir Test',
                'dues_amount' => 100,
                'dues_frequency' => 'monthly',
                'vesting_period_days' => 30,
                'required_approvals' => 2,
            ])
            ->call('register');

        Queue::assertPushed(SendNotificationJob::class, function ($job) {
            return $job->type === NotificationType::NewMemberWelcome;
        });
    }
}
