<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\Affiliate;
use App\Models\AffiliateLog;
use App\Models\AffiliateReferral;
use App\Models\AffiliateWithdrawal;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/** Rows for the affiliate system: members, referrals, withdrawal requests, the money trail. */
class AffiliateSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::where('user_type', 'customer')->orderBy('id')->get();
        if ($customers->count() < 3) {
            return;
        }

        $affiliates = $customers->take(2)->map(fn (User $user) => Affiliate::create([
            'user_id' => $user->id,
            'status' => 'approved',
            'balance' => 0,
        ]));

        // One pending signup waiting for approval.
        Affiliate::create([
            'user_id' => $customers[2]->id,
            'status' => 'pending',
            'balance' => 0,
        ]);

        // Referred customers: two brought in by the first affiliate, one by the second.
        $referralNames = [
            ['Nabila Sultana', 'nabila@customers.wowmart.test', 0],
            ['Rezaul Karim', 'rezaul@customers.wowmart.test', 0],
            ['Hasibul Alam', 'hasibul@customers.wowmart.test', 1],
        ];
        $referred = [];
        foreach ($referralNames as [$name, $email, $affiliateIndex]) {
            $user = User::firstOrNew(['email' => $email]);
            if (! $user->exists) {
                $user->fill(['name' => $name, 'password' => 'password']);
                $user->user_type = UserType::Customer;
                $user->save();
            }
            AffiliateReferral::create([
                'affiliate_id' => $affiliates[$affiliateIndex]->id,
                'referred_user_id' => $user->id,
            ]);
            AffiliateLog::create([
                'affiliate_id' => $affiliates[$affiliateIndex]->id,
                'referred_user_id' => $user->id,
                'type' => 'registration',
                'amount' => 50,
                'description' => "Signup bonus for referring {$user->name}",
                'created_at' => Carbon::now()->subDays(random_int(10, 25)),
            ]);
            $referred[] = $user;
        }

        // Commission on referred customers' paid orders.
        $paidOrders = Order::where('payment_status', 'paid')->orderBy('id')->get();
        $paidOrders->take(3)->each(function (Order $order, int $i) use ($affiliates, $referred): void {
            $affiliate = $affiliates[$i % $affiliates->count()];
            AffiliateLog::create([
                'affiliate_id' => $affiliate->id,
                'referred_user_id' => $referred[$i % count($referred)]->id,
                'order_id' => $order->id,
                'type' => 'purchase',
                'amount' => round(((float) $order->order_total) * 0.05, 2),
                'description' => "First-purchase commission on {$order->code}",
                'created_at' => Carbon::parse($order->created_at)->addDay(),
            ]);
        });

        // Balances match the logs; a payout has already settled on the first member.
        foreach ($affiliates as $affiliate) {
            $earned = (float) $affiliate->logs()->whereIn('type', ['registration', 'purchase'])->sum('amount');
            $affiliate->update(['balance' => $earned]);
        }

        AffiliateWithdrawal::create([
            'affiliate_id' => $affiliates[0]->id,
            'amount' => 100,
            'status' => 'pending',
        ]);
        AffiliateWithdrawal::create([
            'affiliate_id' => $affiliates[1]->id,
            'amount' => 80,
            'status' => 'rejected',
            'note' => 'Balance under review after a returned order.',
        ]);
    }
}
