<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OtpRequest;
use App\Models\SmsTemplate;
use App\Services\OtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OtpController extends Controller
{
    public function __construct(private readonly OtpService $otp) {}

    // ------------------------------------------------- OTP Login Configuration

    public function loginConfiguration(): View
    {
        return view('admin.otp.login-configuration', [
            'settings' => OtpService::loginSettings(),
            'targets' => OtpService::LOGIN_TARGETS,
        ]);
    }

    public function saveLoginConfiguration(OtpRequest $request): RedirectResponse
    {
        $this->otp->saveLoginSettings($request->validated());

        return back()->with('status', 'OTP login settings saved.');
    }

    // ------------------------------------------------------ OTP Configurations

    public function configuration(): View
    {
        return view('admin.otp.configuration', [
            'settings' => OtpService::configuration(),
        ]);
    }

    public function saveConfiguration(OtpRequest $request): RedirectResponse
    {
        $this->otp->saveConfiguration($request->validated());

        return back()->with('status', 'OTP configuration saved.');
    }

    // ------------------------------------------------------------ SMS Templates

    public function templates(): View
    {
        return view('admin.otp.templates', [
            'templates' => SmsTemplate::orderBy('name')->get(),
        ]);
    }

    public function updateTemplate(OtpRequest $request, SmsTemplate $template): RedirectResponse
    {
        $this->otp->updateTemplate($template, $request->validated());

        return back()->with('status', "Template \"{$template->name}\" saved.");
    }
}
