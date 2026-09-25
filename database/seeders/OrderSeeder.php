<?php

namespace Database\Seeders;

use App\Enums\DeliveryStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * A handful of demo orders spread across all six delivery states, so every
 * filter on the admin order screens has something to show.
 *
 * Customers and addresses are fictitious. Stock is not decremented: these
 * represent sales that already happened, and the seeded stock levels are the
 * post-sale figures.
 */
class OrderSeeder extends Seeder
{
    private const CUSTOMERS = [
        ['Rafi Ahmed', 'rafi@customers.wowmart.test', 'House 12, Road 4, GEC Circle', 'Chattogram'],
        ['Nusrat Jahan', 'nusrat@customers.wowmart.test', 'Flat 3B, Block C, Mirpur 10', 'Dhaka'],
        ['Tanvir Hasan', 'tanvir@customers.wowmart.test', 'Station Road, Zindabazar', 'Sylhet'],
    ];

    /** [customer index, status, paid?, days ago, [product index => qty]] */
    private const ORDERS = [
        [0, DeliveryStatus::Delivered, true, 13, [0 => 2, 4 => 1]],
        [1, DeliveryStatus::Delivered, true, 12, [2 => 5]],
        [2, DeliveryStatus::Delivered, true, 11, [8 => 2, 10 => 3]],
        [0, DeliveryStatus::Delivered, true, 10, [13 => 6]],
        [1, DeliveryStatus::Delivered, true, 8, [16 => 2, 20 => 1]],
        [2, DeliveryStatus::Delivered, true, 7, [1 => 4]],
        [0, DeliveryStatus::OnTheWay, true, 5, [7 => 1, 11 => 2]],
        [1, DeliveryStatus::OnTheWay, true, 4, [22 => 3]],
        [2, DeliveryStatus::PickedUp, false, 3, [3 => 1]],
        [0, DeliveryStatus::Confirmed, false, 2, [9 => 1, 18 => 2]],
        [1, DeliveryStatus::Pending, false, 1, [1 => 3, 6 => 1]],
        [2, DeliveryStatus::Pending, false, 0, [12 => 4]],
        [1, DeliveryStatus::Cancelled, false, 9, [5 => 1]],
    ];

    public function run(): void
    {
        $customers = array_map(function (array $c) {
            return User::firstOrCreate(['email' => $c[1]], ['name' => $c[0], 'password' => 'password']);
        }, self::CUSTOMERS);

        $products = Product::orderBy('id')->get()->values();

        foreach (self::ORDERS as $n => [$who, $status, $paid, $daysAgo, $lines]) {
            [$name, , $street, $city] = self::CUSTOMERS[$who];
            $placedAt = now()->subDays($daysAgo)->setTime(10 + $n, 15);
            $total = 0.0;

            $order = new Order([
                'user_id' => $customers[$who]->id,
                'shipping_address' => [
                    'name' => $name, 'phone' => '+8801700000'.str_pad((string) $n, 3, '0', STR_PAD_LEFT),
                    'address' => $street, 'city' => $city, 'country' => 'Bangladesh',
                ],
                'payment_type' => 'cash_on_delivery',
            ]);
            $order->forceFill([
                'code' => $placedAt->format('Ymd-His').str_pad((string) $n, 2, '0', STR_PAD_LEFT),
                'delivery_status' => $status,
                'payment_status' => $paid ? PaymentStatus::Paid : PaymentStatus::Unpaid,
                'created_at' => $placedAt,
                'updated_at' => $placedAt,
            ])->save();

            foreach ($lines as $productIndex => $qty) {
                $product = $products[$productIndex];
                $total += (float) $product->unit_price * $qty;

                $detail = $order->details()->make([
                    'seller_id' => $product->added_by === 'seller' ? $product->user_id : null,
                    'product_id' => $product->id,
                    'price' => $product->unit_price,
                    'quantity' => $qty,
                    'shipping_type' => 'home_delivery',
                ]);
                $detail->forceFill(['delivery_status' => $status, 'payment_status' => $order->payment_status])->save();
            }

            $order->forceFill(['grand_total' => round($total + 60, 2)])->save(); // + flat delivery
        }

        $this->command?->info(sprintf('Orders: %d orders for %d customers.', count(self::ORDERS), count($customers)));
    }
}
