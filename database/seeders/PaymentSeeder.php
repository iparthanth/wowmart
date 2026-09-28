<?php

namespace Database\Seeders;

use App\Models\ManualPaymentMethod;
use App\Models\OfflinePackagePayment;
use App\Models\Order;
use App\Models\Upload;
use App\Models\User;
use App\Models\WalletRecharge;
use Illuminate\Database\Seeder;

/** Payment gateway rows: manual channels, offline proofs, wallet top-ups, offline orders. */
class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::where('user_type', 'customer')->orderBy('id')->get();
        $sellers = User::where('user_type', 'seller')->orderBy('id')->get();
        $images = Upload::where('type', 'image')->orderBy('id')->get();
        $logo = fn () => $images->isNotEmpty() ? $images->random()->id : null;

        $bkash = ManualPaymentMethod::create([
            'name' => 'bKash manual payment',
            'logo' => $logo(),
            'instructions' => "Send money to 01711-000111 (Merchant).\nThen enter the transaction ID at checkout.",
            'status' => true,
        ]);
        ManualPaymentMethod::create([
            'name' => 'Nagad',
            'logo' => $logo(),
            'instructions' => 'Send to 01811-000222 and keep the receipt photo.',
            'status' => true,
        ]);
        ManualPaymentMethod::create([
            'name' => 'Bank transfer — City Bank',
            'logo' => $logo(),
            'instructions' => "Account: 1402-3344-5566-01, WowMart Ltd.\nUse your order code as the reference.",
            'status' => false,
        ]);

        if ($customers->isNotEmpty()) {
            WalletRecharge::create([
                'user_id' => $customers[0]->id,
                'amount' => 500,
                'payment_method' => $bkash->name,
                'transaction_id' => 'BKS8H2K91LQ',
                'upload_id' => $logo(),
                'approval' => 'pending',
            ]);
            WalletRecharge::create([
                'user_id' => $customers->count() > 1 ? $customers[1]->id : $customers[0]->id,
                'amount' => 1200,
                'payment_method' => 'Nagad',
                'transaction_id' => 'NGD44P0X2M',
                'approval' => 'approved',
            ]);
            WalletRecharge::create([
                'user_id' => $customers->count() > 2 ? $customers[2]->id : $customers[0]->id,
                'amount' => 300,
                'payment_method' => $bkash->name,
                'transaction_id' => 'BKS71AQQ3Z',
                'upload_id' => $logo(),
                'approval' => 'rejected',
            ]);

            OfflinePackagePayment::create([
                'user_id' => $customers[0]->id,
                'package_type' => 'customer',
                'package_name' => 'Gold member — yearly',
                'amount' => 2000,
                'payment_method' => $bkash->name,
                'transaction_id' => 'BKS90KL22AA',
                'upload_id' => $logo(),
                'approval' => 'pending',
            ]);
            OfflinePackagePayment::create([
                'user_id' => $customers->count() > 1 ? $customers[1]->id : $customers[0]->id,
                'package_type' => 'customer',
                'package_name' => 'Silver member — monthly',
                'amount' => 250,
                'payment_method' => 'Nagad',
                'transaction_id' => 'NGD18MM77C',
                'approval' => 'approved',
            ]);
        }

        if ($sellers->isNotEmpty()) {
            OfflinePackagePayment::create([
                'user_id' => $sellers[0]->id,
                'package_type' => 'seller',
                'package_name' => 'Premium seller — yearly',
                'amount' => 9500,
                'payment_method' => 'Bank transfer — City Bank',
                'transaction_id' => 'CBL33092HH',
                'upload_id' => $logo(),
                'approval' => 'pending',
            ]);
        }

        // Orders paid through the manual channel, waiting for verification.
        $paid = Order::where('payment_status', 'paid')->orderBy('id')->get();
        $paid->take(2)->each(function (Order $order, int $i): void {
            $order->update([
                'payment_type' => 'manual_payment',
                'payment_status' => $i === 0 ? 'paid' : 'unpaid',
            ]);
        });
    }
}
