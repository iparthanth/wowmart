<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CustomAlertRequest;
use App\Http\Requests\Admin\DynamicPopupRequest;
use App\Http\Requests\Admin\EmailTemplateRequest;
use App\Http\Requests\Admin\NewsletterRequest;
use App\Http\Resources\V1\CustomAlertResource;
use App\Http\Resources\V1\DynamicPopupResource;
use App\Http\Resources\V1\EmailTemplateResource;
use App\Http\Resources\V1\MarketingResource;
use App\Models\CustomAlert;
use App\Models\DynamicPopup;
use App\Models\EmailTemplate;
use App\Models\Newsletter;
use App\Models\Subscriber;
use App\Services\MarketingService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

/** The Marketing section over the API. Update is PUT only. */
class MarketingController extends Controller
{
    public function __construct(private readonly MarketingService $marketing) {}

    // ---------------------------------------------------------------- pop-ups

    public function popupIndex(Request $request): AnonymousResourceCollection
    {
        return DynamicPopupResource::collection(
            DynamicPopup::with('bannerFile')->orderBy('title')->paginate(min(max($request->integer('per_page', 15), 1), 100))->withQueryString()
        );
    }

    public function popupStore(DynamicPopupRequest $request): DynamicPopupResource
    {
        return new DynamicPopupResource($this->marketing->createPopup($request->validated())->load('bannerFile')); // 201
    }

    public function popupShow(DynamicPopup $dynamic_popup): DynamicPopupResource
    {
        return new DynamicPopupResource($dynamic_popup->load('bannerFile'));
    }

    public function popupUpdate(DynamicPopupRequest $request, DynamicPopup $dynamic_popup): DynamicPopupResource
    {
        return new DynamicPopupResource($this->marketing->updatePopup($dynamic_popup, $request->validated())->load('bannerFile'));
    }

    public function popupDestroy(DynamicPopup $dynamic_popup): Response
    {
        $this->marketing->deletePopup($dynamic_popup);

        return response()->noContent();
    }

    // ----------------------------------------------------------------- alerts

    public function alertIndex(Request $request): AnonymousResourceCollection
    {
        return CustomAlertResource::collection(
            CustomAlert::orderBy('title')->paginate(min(max($request->integer('per_page', 15), 1), 100))->withQueryString()
        );
    }

    public function alertStore(CustomAlertRequest $request): CustomAlertResource
    {
        return new CustomAlertResource($this->marketing->createAlert($request->validated())); // 201
    }

    public function alertShow(CustomAlert $custom_alert): CustomAlertResource
    {
        return new CustomAlertResource($custom_alert);
    }

    public function alertUpdate(CustomAlertRequest $request, CustomAlert $custom_alert): CustomAlertResource
    {
        return new CustomAlertResource($this->marketing->updateAlert($custom_alert, $request->validated()));
    }

    public function alertDestroy(CustomAlert $custom_alert): Response
    {
        $this->marketing->deleteAlert($custom_alert);

        return response()->noContent();
    }

    // ------------------------------------------------------- email templates

    public function templateIndex(Request $request): AnonymousResourceCollection
    {
        return EmailTemplateResource::collection(
            EmailTemplate::query()
                ->when(in_array($request->query('receiver'), EmailTemplate::RECEIVERS, true), fn ($q) => $q->where('receiver', $request->query('receiver')))
                ->orderBy('receiver')->orderBy('identifier')
                ->paginate(min(max($request->integer('per_page', 15), 1), 100))
                ->withQueryString()
        );
    }

    public function templateStore(EmailTemplateRequest $request): EmailTemplateResource
    {
        return new EmailTemplateResource($this->marketing->createTemplate($request->validated())); // 201
    }

    public function templateShow(EmailTemplate $email_template): EmailTemplateResource
    {
        return new EmailTemplateResource($email_template);
    }

    public function templateUpdate(EmailTemplateRequest $request, EmailTemplate $email_template): EmailTemplateResource
    {
        return new EmailTemplateResource($this->marketing->updateTemplate($email_template, $request->validated()));
    }

    public function templateDestroy(EmailTemplate $email_template): Response
    {
        $this->marketing->deleteTemplate($email_template);

        return response()->noContent();
    }

    // ------------------------------------------------------ subscribers etc.

    public function subscriberIndex(Request $request): AnonymousResourceCollection
    {
        return MarketingResource::collection(
            Subscriber::orderBy('email')->paginate(min(max($request->integer('per_page', 15), 1), 100))->withQueryString()
        );
    }

    public function subscriberDestroy(Subscriber $subscriber): Response
    {
        $this->marketing->deleteSubscriber($subscriber);

        return response()->noContent();
    }

    public function newsletterIndex(Request $request): AnonymousResourceCollection
    {
        return MarketingResource::collection(
            Newsletter::orderByDesc('created_at')->paginate(min(max($request->integer('per_page', 15), 1), 100))->withQueryString()
        );
    }

    public function newsletterStore(NewsletterRequest $request): MarketingResource
    {
        return new MarketingResource($this->marketing->createNewsletter($request->validated())); // 201
    }

    /** Queue-send a draft; 409 when it already went out. */
    public function newsletterSend(Newsletter $newsletter): MarketingResource
    {
        $this->marketing->sendNewsletter($newsletter);

        return new MarketingResource($newsletter->refresh());
    }

    public function newsletterDestroy(Newsletter $newsletter): Response
    {
        $this->marketing->deleteNewsletter($newsletter);

        return response()->noContent();
    }
}
