<?php

namespace App\Services;

use App\Models\BulkSmsMessage;
use App\Models\BusinessSetting;
use App\Models\CustomNotification;
use App\Models\NotificationType;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

/** Every notification write: settings, types, custom announcements, bulk SMS. */
class NotificationService
{
    /** Notification settings: which types are on and which design they render with. */
    public const SHOW_TYPES = ['text', 'design1', 'design2', 'design3'];

    public const DEFAULT_USER_TYPES = ['order', 'delivery', 'refund', 'wallet', 'promotion'];

    /** @return array{types: array<int, string>, show_type: string} */
    public static function settings(): array
    {
        return [
            'types' => json_decode((string) BusinessSetting::get('notification_types', json_encode(self::DEFAULT_USER_TYPES)), true) ?? self::DEFAULT_USER_TYPES,
            'show_type' => (string) BusinessSetting::get('notification_show_type', 'text'),
        ];
    }

    public function saveSettings(array $data): void
    {
        BusinessSetting::put('notification_types', json_encode(array_values($data['types'] ?? [])));
        BusinessSetting::put('notification_show_type', $data['notification_show_type']);
    }

    // ----------------------------------------------------------------- types

    public function saveType(array $data, ?NotificationType $type = null): NotificationType
    {
        return DB::transaction(fn () => tap($type ?? new NotificationType, fn ($t) => $t->fill($data)->save()));
    }

    public function deleteType(NotificationType $type): void
    {
        if ($type->customNotifications()->exists()) {
            throw new DomainException('This notification type has sent announcements and cannot be deleted.');
        }
        $type->delete();
    }

    // ---------------------------------------------------- custom notifications

    /** Sends one announcement to the given users and returns it for the history. */
    public function sendCustom(array $data, array $userIds): CustomNotification
    {
        if ($userIds === []) {
            throw new DomainException('Pick at least one user to notify.');
        }

        return DB::transaction(function () use ($data, $userIds): CustomNotification {
            $notification = CustomNotification::create($data);
            // Ignore ids that are not real users instead of failing the whole send.
            $existing = User::whereIn('id', $userIds)->pluck('id');
            if ($existing->isEmpty()) {
                throw new DomainException('None of the picked users exist.');
            }
            $notification->users()->sync($existing);

            return $notification;
        });
    }

    // ----------------------------------------------------------------- bulk SMS

    /** Sends the content to every phone given; phones are stored with their outcome. */
    public function sendBulkSms(string $content, ?string $templateId, array $phones): BulkSmsMessage
    {
        $phones = array_values(array_unique(array_map(
            fn (string $phone) => trim($phone),
            $phones,
        )));
        $phones = array_filter($phones, fn (string $p) => $p !== '');
        if ($phones === []) {
            throw new DomainException('Enter at least one phone number.');
        }

        return DB::transaction(function () use ($content, $templateId, $phones): BulkSmsMessage {
            $message = BulkSmsMessage::create([
                'content' => $content,
                'template_id' => $templateId,
                'recipient_count' => count($phones),
            ]);

            $users = User::whereIn('phone', $phones)->pluck('id', 'phone');
            foreach ($phones as $phone) {
                $message->recipients()->create([
                    'user_id' => $users->get($phone),
                    'phone' => $phone,
                    'status' => 'sent',
                ]);
            }

            return $message;
        });
    }
}
