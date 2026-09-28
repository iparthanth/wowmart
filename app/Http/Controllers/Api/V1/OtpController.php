<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OtpRequest;
use App\Http\Resources\V1\SmsTemplateResource;
use App\Models\SmsTemplate;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OtpController extends Controller
{
    public function __construct(private readonly OtpService $otp) {}

    // --------------------------------------------------------------- settings

    public function loginShow(): array
    {
        return ['data' => OtpService::loginSettings()];
    }

    public function loginUpdate(OtpRequest $request): array
    {
        $this->otp->saveLoginSettings($request->validated());

        return ['data' => OtpService::loginSettings()];
    }

    public function configurationShow(): array
    {
        return ['data' => OtpService::configuration()];
    }

    public function configurationUpdate(OtpRequest $request): array
    {
        $this->otp->saveConfiguration($request->validated());

        return ['data' => OtpService::configuration()];
    }

    // ---------------------------------------------------------- SMS templates

    public function templatesIndex(Request $request): AnonymousResourceCollection
    {
        return SmsTemplateResource::collection(
            SmsTemplate::query()
                ->when($request->query('status') !== null && $request->query('status') !== '', fn ($q) => $q->where('status', (bool) $request->query('status')))
                ->orderBy('name')
                ->paginate(min(max($request->integer('per_page', 15), 1), 100))
                ->withQueryString()
        );
    }

    public function templatesShow(SmsTemplate $template): SmsTemplateResource
    {
        return new SmsTemplateResource($template);
    }

    public function templatesUpdate(OtpRequest $request, SmsTemplate $template): SmsTemplateResource
    {
        return new SmsTemplateResource($this->otp->updateTemplate($template, $request->validated()));
    }
}
