<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\DeliveryBoyCollection;
use App\Models\DeliveryBoyPayment;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Two delivery boys plus a small money trail, so the history screens are not
 * empty on a fresh install. Deterministic like the other seeders.
 */
class DeliveryBoySeeder extends Seeder
{
    public function run(): void
    {
        $ridwan = User::firstOrCreate(
            ['email' => 'ridwan@wowmart.test'],
            ['name' => 'Ridwan Hassan', 'phone' => '+8801700000101', 'password' => 'password'],
        );
        $ridwan->user_type = UserType::DeliveryBoy;
        $ridwan->save();

        $jamal = User::firstOrCreate(
            ['email' => 'jamal@wowmart.test'],
            ['name' => 'Jamal Uddin', 'phone' => '+8801700000102', 'password' => 'password'],
        );
        $jamal->user_type = UserType::DeliveryBoy;
        $jamal->save();

        if (DeliveryBoyPayment::count() === 0) {
            DeliveryBoyPayment::create(['delivery_boy_id' => $ridwan->id, 'amount' => 120, 'note' => 'Weekly payout', 'paid_at' => now()->subDays(3)]);
        }

        if (DeliveryBoyCollection::count() === 0) {
            DeliveryBoyCollection::create(['delivery_boy_id' => $jamal->id, 'amount' => 75.5, 'note' => 'Cash from orders #101, #102', 'collected_at' => now()->subDay()]);
        }
    }
}
