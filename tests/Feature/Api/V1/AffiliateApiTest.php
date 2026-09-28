<?php

namespace Tests\Feature\Api\V1;

use App\Models\Affiliate;
use App\Models\AffiliateWithdrawal;
use App\Models\BusinessSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AffiliateApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Sanctum::actingAs(User::where('email', 'admin@wowmart.test')->firstOrFail());
    }

    public function test_settings_round_trip(): void
    {
        $this->getJson('/api/v1/affiliate/settings')
            ->assertOk()
            ->assertJsonPath('data.affiliate_first_purchase_value', '5');

        $this->putJson('/api/v1/affiliate/settings', [
            'affiliate_first_purchase_status' => '1',
            'affiliate_first_purchase_type' => 'amount',
            'affiliate_first_purchase_value' => '120',
            'affiliate_sharing_status' => '0',
            'affiliate_sharing_type' => 'percent',
            'affiliate_sharing_value' => '3',
            'affiliate_category_status' => '1',
            'affiliate_validation_days' => '21',
        ])->assertOk()->assertJsonPath('data.affiliate_first_purchase_value', '120');

        $this->assertSame('120', BusinessSetting::get('affiliate_first_purchase_value'));

        // Conflict rule returns 409 (bootstrap maps DomainException on API).
        $this->putJson('/api/v1/affiliate/settings', [
            'affiliate_first_purchase_status' => '1',
            'affiliate_first_purchase_type' => 'percent',
            'affiliate_first_purchase_value' => '5',
            'affiliate_sharing_status' => '1',
            'affiliate_sharing_type' => 'percent',
            'affiliate_sharing_value' => '3',
            'affiliate_category_status' => '1',
            'affiliate_validation_days' => '30',
        ])->assertConflict();
    }

    public function test_registration_form_round_trip(): void
    {
        $this->putJson('/api/v1/affiliate/registration', [
            'affiliate_registration_enabled' => '1',
            'fields' => [
                ['label' => 'Channel link', 'type' => 'text'],
            ],
        ])->assertOk()->assertJsonPath('data.fields.0.label', 'Channel link');
    }

    public function test_affiliate_users_round_trip(): void
    {
        $this->getJson('/api/v1/affiliate/users')->assertOk();

        $pending = Affiliate::where('status', 'pending')->firstOrFail();
        $id = $pending->id;

        $this->getJson("/api/v1/affiliate/users/{$id}")
            ->assertOk()
            ->assertJsonPath('data.code', $pending->code)
            ->assertJsonPath('data.user.email', $pending->user->email);

        $this->putJson("/api/v1/affiliate/users/{$id}", ['status' => 'approved'])
            ->assertOk()->assertJsonPath('data.status', 'approved');

        // Already approved -> 409.
        $this->putJson("/api/v1/affiliate/users/{$id}", ['status' => 'approved'])->assertConflict();

        $this->deleteJson("/api/v1/affiliate/users/{$id}")->assertNoContent();
        $this->assertModelMissing($pending);
    }

    public function test_withdrawals_round_trip(): void
    {
        $this->getJson('/api/v1/affiliate/withdrawals')->assertOk();

        $withdrawal = AffiliateWithdrawal::where('status', 'pending')->firstOrFail();
        $id = $withdrawal->id;

        $this->putJson("/api/v1/affiliate/withdrawals/{$id}", [
            'status' => 'approved',
        ])->assertOk()->assertJsonPath('data.status', 'approved');

        $this->putJson("/api/v1/affiliate/withdrawals/{$id}", ['status' => 'rejected'])->assertConflict();

        $this->patchJson("/api/v1/affiliate/withdrawals/{$id}", ['status' => 'rejected'])->assertMethodNotAllowed();

        $this->deleteJson("/api/v1/affiliate/withdrawals/{$id}")->assertNoContent();
    }

    public function test_referrals_and_logs_are_readable(): void
    {
        $this->getJson('/api/v1/affiliate/referrals')
            ->assertOk()
            ->assertJsonPath('meta.total', 3);

        $this->getJson('/api/v1/affiliate/logs?type=registration')
            ->assertOk()
            ->assertJsonPath('meta.total', 3);
    }

    public function test_the_affiliate_api_needs_an_admin(): void
    {
        Sanctum::actingAs(User::where('email', 'rafi@customers.wowmart.test')->firstOrFail());

        $this->getJson('/api/v1/affiliate/users')->assertForbidden();
        $this->putJson('/api/v1/affiliate/settings', ['affiliate_validation_days' => '10'])->assertForbidden();
    }
}
