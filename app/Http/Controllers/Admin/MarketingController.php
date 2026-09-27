<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CustomAlertRequest;
use App\Http\Requests\Admin\DynamicPopupRequest;
use App\Http\Requests\Admin\EmailTemplateRequest;
use App\Http\Requests\Admin\NewsletterRequest;
use App\Models\CustomAlert;
use App\Models\DynamicPopup;
use App\Models\EmailTemplate;
use App\Models\Newsletter;
use App\Models\Subscriber;
use App\Services\MarketingService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** The Marketing section: pop-ups, alerts, email templates, subscribers, newsletters. */
class MarketingController extends Controller
{
    public function __construct(private readonly MarketingService $marketing) {}

    // ---------------------------------------------------------------- pop-ups

    public function popups(): View
    {
        return view('admin.marketing.popups', [
            'popups' => DynamicPopup::orderBy('title')->paginate(20),
            'newPopup' => new DynamicPopup,
            'pages' => DynamicPopup::PAGES,
        ]);
    }

    public function storePopup(DynamicPopupRequest $request): RedirectResponse
    {
        $popup = $this->marketing->createPopup($request->validated());

        return redirect()->route('admin.marketing.popups')->with('status', "Pop-up \"{$popup->title}\" added.");
    }

    public function editPopup(DynamicPopup $dynamic_popup): View
    {
        return view('admin.marketing.popup-edit', [
            'popup' => $dynamic_popup,
            'pages' => DynamicPopup::PAGES,
        ]);
    }

    public function updatePopup(DynamicPopupRequest $request, DynamicPopup $dynamic_popup): RedirectResponse
    {
        $this->marketing->updatePopup($dynamic_popup, $request->validated());

        return redirect()->route('admin.marketing.popups.edit', $dynamic_popup)->with('status', 'Changes saved.');
    }

    public function destroyPopup(DynamicPopup $dynamic_popup): RedirectResponse
    {
        $this->marketing->deletePopup($dynamic_popup);

        return redirect()->route('admin.marketing.popups')->with('status', 'Pop-up deleted.');
    }

    // ----------------------------------------------------------------- alerts

    public function alerts(): View
    {
        return view('admin.marketing.alerts', [
            'alerts' => CustomAlert::orderBy('title')->paginate(20),
            'newAlert' => new CustomAlert,
            'styles' => CustomAlert::STYLES,
        ]);
    }

    public function storeAlert(CustomAlertRequest $request): RedirectResponse
    {
        $alert = $this->marketing->createAlert($request->validated());

        return redirect()->route('admin.marketing.alerts')->with('status', "Alert \"{$alert->title}\" added.");
    }

    public function editAlert(CustomAlert $custom_alert): View
    {
        return view('admin.marketing.alert-edit', [
            'alert' => $custom_alert,
            'styles' => CustomAlert::STYLES,
        ]);
    }

    public function updateAlert(CustomAlertRequest $request, CustomAlert $custom_alert): RedirectResponse
    {
        $this->marketing->updateAlert($custom_alert, $request->validated());

        return redirect()->route('admin.marketing.alerts.edit', $custom_alert)->with('status', 'Changes saved.');
    }

    public function destroyAlert(CustomAlert $custom_alert): RedirectResponse
    {
        $this->marketing->deleteAlert($custom_alert);

        return redirect()->route('admin.marketing.alerts')->with('status', 'Alert deleted.');
    }

    // ------------------------------------------------------- email templates

    /** The template list, optionally filtered by receiver from the query string. */
    public function templates(Request $request): View
    {
        $receiver = $request->query('receiver');

        return view('admin.marketing.templates', [
            'templates' => EmailTemplate::query()
                ->when(is_string($receiver) && in_array($receiver, EmailTemplate::RECEIVERS, true), fn ($q) => $q->where('receiver', $receiver))
                ->orderBy('receiver')->orderBy('identifier')
                ->paginate(20)
                ->withQueryString(),
            'newTemplate' => new EmailTemplate,
            'receivers' => EmailTemplate::RECEIVERS,
            'receiver' => $receiver,
        ]);
    }

    public function storeTemplate(EmailTemplateRequest $request): RedirectResponse
    {
        try {
            $template = $this->marketing->createTemplate($request->validated());
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('admin.marketing.templates')->with('status', "Template \"{$template->identifier}\" added.");
    }

    public function editTemplate(EmailTemplate $email_template): View
    {
        return view('admin.marketing.template-edit', [
            'template' => $email_template,
            'receivers' => EmailTemplate::RECEIVERS,
        ]);
    }

    public function updateTemplate(EmailTemplateRequest $request, EmailTemplate $email_template): RedirectResponse
    {
        try {
            $this->marketing->updateTemplate($email_template, $request->validated());
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('admin.marketing.templates.edit', $email_template)->with('status', 'Changes saved.');
    }

    public function destroyTemplate(EmailTemplate $email_template): RedirectResponse
    {
        $this->marketing->deleteTemplate($email_template);

        return redirect()->route('admin.marketing.templates')->with('status', 'Template deleted.');
    }

    // ------------------------------------------------------ subscribers etc.

    public function subscribers(): View
    {
        return view('admin.marketing.subscribers', [
            'subscribers' => Subscriber::orderBy('email')->paginate(25),
        ]);
    }

    public function deleteSubscriber(Subscriber $subscriber): RedirectResponse
    {
        $this->marketing->deleteSubscriber($subscriber);

        return redirect()->route('admin.marketing.subscribers')->with('status', 'Subscriber removed.');
    }

    public function newsletters(): View
    {
        return view('admin.marketing.newsletters', [
            'newsletters' => Newsletter::orderByDesc('created_at')->paginate(20),
            'newNewsletter' => new Newsletter,
            'segments' => Newsletter::SEGMENTS,
        ]);
    }

    public function storeNewsletter(NewsletterRequest $request): RedirectResponse
    {
        $newsletter = $this->marketing->createNewsletter($request->validated());

        return redirect()->route('admin.marketing.newsletters')->with('status', "Newsletter \"{$newsletter->subject}\" saved as a draft.");
    }

    /** Queue-send a draft; a sent newsletter can never go out twice. */
    public function sendNewsletter(Newsletter $newsletter): RedirectResponse
    {
        try {
            $this->marketing->sendNewsletter($newsletter);
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.marketing.newsletters')->with('status', "Newsletter \"{$newsletter->subject}\" queued for sending.");
    }

    public function deleteNewsletter(Newsletter $newsletter): RedirectResponse
    {
        $this->marketing->deleteNewsletter($newsletter);

        return redirect()->route('admin.marketing.newsletters')->with('status', 'Newsletter deleted.');
    }
}
