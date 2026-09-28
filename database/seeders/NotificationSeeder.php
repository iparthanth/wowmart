<?php

namespace Database\Seeders;

use App\Models\BulkSmsMessage;
use App\Models\CustomNotification;
use App\Models\NotificationType;
use App\Models\Upload;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Database\Seeder;

/** Notification types, one staff announcement, one SMS blast. */
class NotificationSeeder extends Seeder
{
    private const TYPES = [
        ['Order placed', 'Your order {order_code} is confirmed. Thanks for shopping with WowMart!'],
        ['Order shipped', 'Good news — order {order_code} is on its way.'],
        ['Refund approved', 'Your refund for {order_code} is approved.'],
        ['Wallet recharged', '৳{amount} added to your WowMart wallet.'],
        ['Promotion', 'Mega sale starts tonight — up to 60% off!'],
    ];

    public function run(): void
    {
        $images = Upload::where('type', 'image')->orderBy('id')->get();
        $imageId = fn () => $images->isNotEmpty() ? $images->random()->id : null;

        foreach (self::TYPES as [$name, $text]) {
            NotificationType::updateOrCreate(['name' => $name], [
                'image' => $imageId(),
                'default_text' => $text,
                'status' => true,
            ]);
        }

        // Settings follow the reference defaults.
        NotificationService::settings();
        app(NotificationService::class)->saveSettings([
            'types' => NotificationService::DEFAULT_USER_TYPES,
            'notification_show_type' => 'design1',
        ]);

        $customers = User::where('user_type', 'customer')->orderBy('id')->get();
        $orderType = NotificationType::where('name', 'Order placed')->first();

        if ($customers->isNotEmpty() && $orderType) {
            $announcement = CustomNotification::create([
                'notification_type_id' => $orderType->id,
                'content' => 'Weekend flash sale — everything ships free!',
                'link' => '/campaign/weekend-flash-sale',
            ]);
            $announcement->users()->sync($customers->take(3)->pluck('id'));
        }

        $phones = ['+8801711000011', '+8801811000022', '+8801911000033'];
        $message = BulkSmsMessage::create([
            'content' => 'WowMart: Winter sale live now. Up to 60% off sitewide. Shop today!',
            'template_id' => '100741690001',
            'recipient_count' => count($phones),
        ]);
        foreach ($phones as $phone) {
            $message->recipients()->create([
                'user_id' => User::where('phone', $phone)->value('id'),
                'phone' => $phone,
                'status' => 'sent',
            ]);
        }
    }
}
