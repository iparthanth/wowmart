<?php

namespace App\Services;

use App\Enums\UserType;
use App\Jobs\SendNewsletter;
use App\Models\CustomAlert;
use App\Models\DynamicPopup;
use App\Models\EmailTemplate;
use App\Models\Newsletter;
use App\Models\Subscriber;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

/** Every write to marketing content. Input is validated already. */
class MarketingService
{
    // ---------------------------------------------------------------- pop-ups

    public function createPopup(array $data): DynamicPopup
    {
        return DB::transaction(fn () => DynamicPopup::create($data));
    }

    public function updatePopup(DynamicPopup $popup, array $data): DynamicPopup
    {
        return DB::transaction(fn () => tap($popup)->update($data));
    }

    public function deletePopup(DynamicPopup $popup): void
    {
        $popup->delete();
    }

    // ----------------------------------------------------------------- alerts

    public function createAlert(array $data): CustomAlert
    {
        return DB::transaction(fn () => CustomAlert::create($data));
    }

    public function updateAlert(CustomAlert $alert, array $data): CustomAlert
    {
        return DB::transaction(fn () => tap($alert)->update($data));
    }

    public function deleteAlert(CustomAlert $alert): void
    {
        $alert->delete();
    }

    // ------------------------------------------------------- email templates

    public function createTemplate(array $data): EmailTemplate
    {
        return DB::transaction(function () use ($data) {
            $this->assertTemplateFree($data['receiver'], $data['identifier']);

            return EmailTemplate::create($data);
        });
    }

    public function updateTemplate(EmailTemplate $template, array $data): EmailTemplate
    {
        return DB::transaction(function () use ($template, $data) {
            $this->assertTemplateFree($data['receiver'], $data['identifier'], $template);

            return tap($template)->update($data);
        });
    }

    /**
     * @throws DomainException when another template already covers the event
     */
    public function assertTemplateFree(string $receiver, string $identifier, ?EmailTemplate $ignore = null): void
    {
        $taken = EmailTemplate::where('receiver', $receiver)->where('identifier', $identifier)
            ->when($ignore, fn ($q) => $q->whereKeyNot($ignore->id))
            ->exists();

        if ($taken) {
            throw new DomainException("The {$receiver} already has a template for \"{$identifier}\". Edit that one instead.");
        }
    }

    public function deleteTemplate(EmailTemplate $template): void
    {
        $template->delete();
    }

    // ------------------------------------------------------ subscribers etc.

    /** The footer opt-in; a duplicate email is a no-op, not an error. */
    public function subscribe(string $email): Subscriber
    {
        return DB::transaction(function () use ($email) {
            $existing = Subscriber::where('email', $email)->first();

            if ($existing) {
                return $existing;
            }

            return Subscriber::create(['email' => $email, 'status' => true]);
        });
    }

    public function setSubscriberStatus(Subscriber $subscriber, bool $status): Subscriber
    {
        return DB::transaction(fn () => tap($subscriber)->update(['status' => $status]));
    }

    public function deleteSubscriber(Subscriber $subscriber): void
    {
        $subscriber->delete();
    }

    public function createNewsletter(array $data): Newsletter
    {
        return DB::transaction(fn () => Newsletter::create($data));
    }

    public function updateNewsletter(Newsletter $newsletter, array $data): Newsletter
    {
        return DB::transaction(fn () => tap($newsletter)->update($data));
    }

    /**
     * Queues the send and stamps it; re-sending is refused so a newsletter
     * never goes out twice.
     *
     * @throws DomainException when the newsletter already went out
     */
    public function sendNewsletter(Newsletter $newsletter): Newsletter
    {
        if ($newsletter->sent_at !== null) {
            throw new DomainException('This newsletter was already sent on '.$newsletter->sent_at->format('j M Y, g:i a').'.');
        }

        return DB::transaction(function () use ($newsletter) {
            $recipients = $this->segment($newsletter->segment);
            $newsletter->forceFill(['sent_at' => now()]);
            $newsletter->save();

            // Mail driver is "log" locally; the queued job renders the template.
            SendNewsletter::dispatch($newsletter, $recipients);

            return $newsletter;
        });
    }

    /** @return list<string> the emails a segment covers */
    private function segment(string $segment): array
    {
        return match ($segment) {
            'subscribers' => Subscriber::active()->pluck('email')->all(),
            'customers' => User::where('user_type', UserType::Customer)->pluck('email')->all(),
            default => array_values(array_unique(array_merge(
                Subscriber::active()->pluck('email')->all(),
                User::where('user_type', UserType::Customer)->pluck('email')->all(),
            ))),
        };
    }

    public function deleteNewsletter(Newsletter $newsletter): void
    {
        $newsletter->delete();
    }
}
