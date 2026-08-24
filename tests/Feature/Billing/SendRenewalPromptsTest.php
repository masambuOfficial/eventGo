<?php

namespace Tests\Feature\Billing;

use App\Domain\Billing\Models\Plan;
use App\Domain\Billing\Models\Subscription;
use App\Domain\Notifications\Models\Notification;
use App\Domain\Providers\Models\Provider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SendRenewalPromptsTest extends TestCase
{
    use RefreshDatabase;

    public function test_notifies_the_provider_owner_at_each_threshold(): void
    {
        $plan = Plan::factory()->create();
        $provider = Provider::factory()->create();
        Subscription::factory()->create([
            'subscriber_id' => $provider->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'expires_at' => now()->addDays(7),
        ]);

        Artisan::call('subscriptions:renewal-prompts');

        $this->assertDatabaseHas('notifications', [
            'user_id' => $provider->owner_user_id,
            'type' => 'subscription_renewal',
        ]);
    }

    public function test_running_twice_in_the_same_day_does_not_duplicate_the_notification(): void
    {
        $plan = Plan::factory()->create();
        $provider = Provider::factory()->create();
        Subscription::factory()->create([
            'subscriber_id' => $provider->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'expires_at' => now()->addDays(14),
        ]);

        Artisan::call('subscriptions:renewal-prompts');
        Artisan::call('subscriptions:renewal-prompts');

        $count = Notification::where('user_id', $provider->owner_user_id)
            ->where('type', 'subscription_renewal')
            ->count();

        $this->assertSame(1, $count);
    }
}
