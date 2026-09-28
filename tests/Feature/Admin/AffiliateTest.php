<?php

namespace Tests\Feature\Admin;

use App\Models\Affiliate;
use App\Models\AffiliateWithdrawal;
use App\Models\BusinessSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AffiliateTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->admin = User::where('email', 'admin@wowmart.test')->firstOrFail();
    }

    public function test_the_six_affiliate_screens_render(): void
    {
        $this->actingAs($this->admin)->get('/admin/affiliate')
            ->assertOk()->assertSee('User registration &amp; first purchase', false);

        $this->actingAs($this->admin)->get('/admin/affiliate/registration')
            ->assertOk()->assertSee('Registration form');

        $this->actingAs($this->admin)->get('/admin/affiliate/users')
            ->assertOk()->assertSee('AFF-');

        $this->actingAs($this->admin)->get('/admin/affiliate/referrals')
            ->assertOk()->assertSee('Referred by');

        $this->actingAs($this->admin)->get('/admin/affiliate/withdrawals')
            ->assertOk()->assertSee('Withdraw requests');

        $this->actingAs($this->admin)->get('/admin/affiliate/logs')
            ->assertOk()->assertSee('Affiliate logs');
    }

    public function test_configurations_are_saved_and_conflicting_modes_are_refused(): void
    {
        $this->actingAs($this->admin)->post('/admin/affiliate', [
            'affiliate_first_purchase_status' => '1',
            'affiliate_first_purchase_type' => 'percent',
            'affiliate_first_purchase_value' => '7.5',
            'affiliate_sharing_status' => '0',
            'affiliate_sharing_type' => 'percent',
            'affiliate_sharing_value' => '3',
            'affiliate_category_status' => '1',
            'affiliate_category_commissions' => [
                ['category_id' => 1, 'type' => 'percent', 'value' => '2'],
            ],
            'affiliate_validation_days' => '45',
        ])->assertRedirect()->assertSessionHas('status');

        $this->assertSame('7.5', BusinessSetting::get('affiliate_first_purchase_value'));
        $this->assertSame('45', BusinessSetting::get('affiliate_validation_days'));
        $this->assertSame('1', BusinessSetting::get('affiliate_category_status'));

        // Product sharing and category wise cannot both be on (reference panel rule).
        $this->actingAs($this->admin)->post('/admin/affiliate', [
            'affiliate_first_purchase_status' => '1',
            'affiliate_first_purchase_type' => 'percent',
            'affiliate_first_purchase_value' => '5',
            'affiliate_sharing_status' => '1',
            'affiliate_sharing_type' => 'percent',
            'affiliate_sharing_value' => '3',
            'affiliate_category_status' => '1',
            'affiliate_validation_days' => '30',
        ])->assertRedirect()->assertSessionHas('error');
        $this->assertSame('0', (string) BusinessSetting::get('affiliate_sharing_status', '0'));
    }

    public function test_the_registration_form_builder_saves_fields(): void
    {
        $this->actingAs($this->admin)->post('/admin/affiliate/registration', [
            'affiliate_registration_enabled' => '1',
            'fields' => [
                ['label' => 'Website or channel', 'type' => 'text'],
                ['label' => 'Audience size', 'type' => 'number'],
            ],
        ])->assertRedirect()->assertSessionHas('status');

        $fields = json_decode(BusinessSetting::get('affiliate_registration_fields'), true);
        $this->assertSame('Website or channel', $fields[0]['label']);
        $this->assertSame('number', $fields[1]['type']);
    }

    public function test_affiliates_are_approved_and_rejected(): void
    {
        $pending = Affiliate::where('status', 'pending')->firstOrFail();

        $this->actingAs($this->admin)->post("/admin/affiliate/users/{$pending->id}/decision", ['status' => 'approved'])
            ->assertRedirect()->assertSessionHas('status');
        $this->assertSame('approved', $pending->fresh()->status);

        $this->actingAs($this->admin)->post("/admin/affiliate/users/{$pending->id}/decision", ['status' => 'approved'])
            ->assertRedirect()->assertSessionHas('error');
    }

    public function test_a_withdrawal_approval_pays_out_and_logs(): void
    {
        $withdrawal = AffiliateWithdrawal::where('status', 'pending')->firstOrFail();
        $affiliate = $withdrawal->affiliate;
        $balanceBefore = (float) $affiliate->balance;

        $this->actingAs($this->admin)->post("/admin/affiliate/withdrawals/{$withdrawal->id}/decision", ['status' => 'approved'])
            ->assertRedirect()->assertSessionHas('status');

        $this->assertSame('approved', $withdrawal->fresh()->status);
        $this->assertEquals($balanceBefore - 100, (float) $affiliate->fresh()->balance);
        $this->assertSame(1, $affiliate->logs()->where('type', 'withdrawal')->count());

        // A decided request cannot be decided again.
        $this->actingAs($this->admin)->post("/admin/affiliate/withdrawals/{$withdrawal->id}/decision", ['status' => 'rejected'])
            ->assertRedirect()->assertSessionHas('error');
    }

    public function test_a_rejected_withdrawal_keeps_the_balance(): void
    {
        $withdrawal = AffiliateWithdrawal::where('status', 'pending')->firstOrFail();
        $balanceBefore = (float) $withdrawal->affiliate->balance;

        $this->actingAs($this->admin)->post("/admin/affiliate/withdrawals/{$withdrawal->id}/decision", [
            'status' => 'rejected', 'note' => 'Requesting amount review first.',
        ])->assertRedirect()->assertSessionHas('status');

        $this->assertSame('rejected', $withdrawal->fresh()->status);
        $this->assertSame('Requesting amount review first.', $withdrawal->fresh()->note);
        $this->assertEquals($balanceBefore, (float) $withdrawal->affiliate->fresh()->balance);
    }
}
