<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\NotificationRequest;
use App\Http\Resources\V1\BulkSmsMessageResource;
use App\Http\Resources\V1\CustomNotificationResource;
use App\Http\Resources\V1\NotificationTypeResource;
use App\Models\BulkSmsMessage;
use App\Models\CustomNotification;
use App\Models\NotificationType;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class NotificationController extends Controller
{
    public function __construct(private readonly NotificationService $notifications) {}

    // ------------------------------------------------------------- settings

    public function settingsShow(): array
    {
        return ['data' => NotificationService::settings()];
    }

    public function settingsUpdate(NotificationRequest $request): array
    {
        $this->notifications->saveSettings($request->validated());

        return ['data' => NotificationService::settings()];
    }

    // ----------------------------------------------------------------- types

    public function typesIndex(Request $request): AnonymousResourceCollection
    {
        return NotificationTypeResource::collection(
            NotificationType::query()
                ->when($request->query('search'), fn ($q, $s) => $q->where('name', 'like', "%{$s}%"))
                ->orderBy('name')
                ->paginate(min(max($request->integer('per_page', 15), 1), 100))
                ->withQueryString()
        );
    }

    public function typesShow(NotificationType $notificationType): NotificationTypeResource
    {
        return new NotificationTypeResource($notificationType->load('imageUpload'));
    }

    public function typesStore(NotificationRequest $request): NotificationTypeResource
    {
        $type = $this->notifications->saveType($request->validated() + ['status' => (int) $request->boolean('status')]);

        return new NotificationTypeResource($type->load('imageUpload'));
    }

    public function typesUpdate(NotificationRequest $request, NotificationType $notificationType): NotificationTypeResource
    {
        $data = $request->validated() + ['status' => (int) $request->boolean('status')];
        $this->notifications->saveType($data, $notificationType);

        return new NotificationTypeResource($notificationType->load('imageUpload'));
    }

    public function typesDestroy(NotificationType $notificationType): Response
    {
        $this->notifications->deleteType($notificationType); // 409 when announcements exist

        return response()->noContent();
    }

    // ---------------------------------------------------- custom notifications

    public function customStore(NotificationRequest $request): CustomNotificationResource
    {
        return new CustomNotificationResource(
            $this->notifications->sendCustom(
                collect($request->validated())->only(['notification_type_id', 'content', 'link'])->all(),
                $request->validated('user_ids'),
            )->load('type'),
        );
    }

    public function customIndex(Request $request): AnonymousResourceCollection
    {
        return CustomNotificationResource::collection(
            CustomNotification::query()
                ->with('type')
                ->withCount('users')
                ->latest('id')
                ->paginate(min(max($request->integer('per_page', 15), 1), 100))
                ->withQueryString()
        );
    }

    public function customDestroy(CustomNotification $customNotification): Response
    {
        $customNotification->users()->detach();
        $customNotification->delete();

        return response()->noContent();
    }

    // ----------------------------------------------------------------- bulk SMS

    public function bulkSmsIndex(Request $request): AnonymousResourceCollection
    {
        return BulkSmsMessageResource::collection(
            BulkSmsMessage::query()
                ->latest('id')
                ->paginate(min(max($request->integer('per_page', 15), 1), 100))
                ->withQueryString()
        );
    }

    public function bulkSmsStore(NotificationRequest $request): BulkSmsMessageResource
    {
        return new BulkSmsMessageResource($this->notifications->sendBulkSms(
            $request->validated('content'),
            $request->validated('template_id'),
            $request->validated('user_phones'),
        ));
    }
}
