<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NotificationRequest;
use App\Models\BulkSmsMessage;
use App\Models\CustomNotification;
use App\Models\NotificationType;
use App\Models\Upload;
use App\Models\User;
use App\Services\NotificationService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(private readonly NotificationService $notifications) {}

    // ---------------------------------------------------------------- settings

    public function settings(): View
    {
        return view('admin.notifications.settings', [
            'settings' => NotificationService::settings(),
        ]);
    }

    public function saveSettings(NotificationRequest $request): RedirectResponse
    {
        $this->notifications->saveSettings($request->validated());

        return back()->with('status', 'Notification settings saved.');
    }

    // ------------------------------------------------------------------- types

    public function types(): View
    {
        return view('admin.notifications.types', [
            'types' => NotificationType::query()->with('imageUpload')->when(request('sort_search'), fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))->orderBy('name')->get(),
            'images' => Upload::where('type', 'image')->latest('id')->get(),
        ]);
    }

    public function storeType(NotificationRequest $request): RedirectResponse
    {
        $this->notifications->saveType($request->validated() + ['status' => (int) $request->boolean('status')]);

        return back()->with('status', 'Notification type added.');
    }

    public function editType(NotificationType $type): View
    {
        return view('admin.notifications.type-edit', [
            'type' => $type->load('imageUpload'),
            'images' => Upload::where('type', 'image')->latest('id')->get(),
        ]);
    }

    public function updateType(NotificationRequest $request, NotificationType $type): RedirectResponse
    {
        $this->notifications->saveType($request->validated() + ['status' => (int) $request->boolean('status')], $type);

        return redirect()->route('admin.notifications.types')->with('status', 'Notification type saved.');
    }

    public function destroyType(NotificationType $type): RedirectResponse
    {
        try {
            $this->notifications->deleteType($type);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Notification type deleted.');
    }

    // ----------------------------------------------------- custom notification

    public function custom(): View
    {
        return view('admin.notifications.custom', [
            'types' => NotificationType::where('status', 1)->with('imageUpload')->orderBy('name')->get(),
            'customers' => User::where('user_type', 'customer')->orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    public function sendCustom(NotificationRequest $request): RedirectResponse
    {
        try {
            $this->notifications->sendCustom(
                collect($request->validated())->only(['notification_type_id', 'content', 'link'])->all(),
                $request->validated('user_ids'),
            );
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Notification sent.');
    }

    public function history(): View
    {
        return view('admin.notifications.history', [
            'notifications' => CustomNotification::query()->with(['type', 'users'])->withCount('users')->latest('id')->paginate(20),
        ]);
    }

    public function destroyCustom(CustomNotification $notification): RedirectResponse
    {
        $notification->users()->detach();
        $notification->delete();

        return back()->with('status', 'Notification removed from the history.');
    }

    // ---------------------------------------------------------------- bulk SMS

    public function bulkSms(): View
    {
        return view('admin.notifications.bulk-sms', [
            'messages' => BulkSmsMessage::withCount('recipients')->latest('id')->paginate(15),
        ]);
    }

    public function sendBulkSms(NotificationRequest $request): RedirectResponse
    {
        try {
            $message = $this->notifications->sendBulkSms(
                $request->validated('content'),
                $request->validated('template_id'),
                $request->validated('user_phones'),
            );
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', "SMS queued for {$message->recipient_count} numbers.");
    }
}
