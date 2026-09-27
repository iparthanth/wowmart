<?php

namespace Database\Seeders;

use App\Models\BusinessSetting;
use App\Models\CommissionLog;
use App\Models\Order;
use App\Models\SearchLog;
use App\Models\User;
use App\Models\WalletRecharge;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/** Rows for the panel-only reports: search terms, commission per paid seller order, wallet recharges. */
class ReportSeeder extends Seeder
{
    /** term => how many visitors typed it. */
    private const SEARCHES = [
        'drone kit' => 14, 'rc car' => 11, 'servo motor' => 9, 'arduino uno' => 8,
        'raspberry pi' => 7, 'lipo battery' => 6, 'fpv camera' => 5, 'sensor kit' => 4,
        '3d printer' => 3, 'soldering iron' => 2, 'led strip' => 2, 'gear motor' => 1,
    ];

    public function run(): void
    {
        $customers = User::where('user_type', 'customer')->orderBy('id')->get();
        if ($customers->isEmpty()) {
            return;
        }

        foreach (self::SEARCHES as $term => $times) {
            foreach (range(1, $times) as $i) {
                SearchLog::create([
                    'query' => $term,
                    'results' => random_int(0, 12),
                    'user_id' => $customers->random()->id,
                    'created_at' => Carbon::now()->subDays(random_int(0, 29))->subHours(random_int(0, 23)),
                ]);
            }
        }

        // One commission row per seller inside each paid order: a multi-vendor
        // order carries the seller on its lines, so the marketplace's cut is
        // computed from the value each seller's lines added (price × quantity),
        // at the seller_commission setting's percentage.
        $rate = (float) BusinessSetting::get('seller_commission', 5);
        Order::query()
            ->where('payment_status', 'paid')
            ->with('details')
            ->get()
            ->each(function (Order $order) use ($rate): void {
                $order->details
                    ->filter(fn ($d) => $d->seller_id !== null)
                    ->groupBy('seller_id')
                    ->each(function ($lines, string $sellerId) use ($order, $rate): void {
                        $value = round($lines->sum(fn ($d) => $d->price * $d->quantity), 2);
                        $commission = round($value * $rate / 100, 2);
                        CommissionLog::create([
                            'order_id' => $order->id,
                            'order_code' => $order->code,
                            'seller_id' => (int) $sellerId,
                            'admin_commission' => $commission,
                            'seller_earning' => round($value - $commission, 2),
                        ]);
                    });
            });

        // Offline top-ups in every state, pending first on the panel.
        foreach ([
            ['amount' => 100, 'payment_method' => 'bank_transfer', 'approval' => 'approved'],
            ['amount' => 50, 'payment_method' => 'bkash', 'approval' => 'approved'],
            ['amount' => 200, 'payment_method' => 'bank_transfer', 'approval' => 'pending'],
            ['amount' => 75, 'payment_method' => 'rocket', 'approval' => 'pending'],
            ['amount' => 30, 'payment_method' => 'bkash', 'approval' => 'rejected'],
        ] as $i => $recharge) {
            WalletRecharge::create($recharge + [
                'user_id' => $customers[$i % $customers->count()]->id,
                'transaction_id' => 'TXN-'.strtoupper(substr(md5($i.'wallet'), 0, 10)),
                'created_at' => Carbon::now()->subDays($i + 1),
            ]);
        }
    }
}
