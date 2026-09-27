<?php

namespace App\Jobs;

use App\Mail\NewsletterMail;
use App\Models\Newsletter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

/** Sends one newsletter issue to its segment; queued so sends never block the panel. */
class SendNewsletter implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /**
     * @param  list<string>  $recipients
     */
    public function __construct(public readonly Newsletter $newsletter, public readonly array $recipients) {}

    public function handle(): void
    {
        foreach ($this->recipients as $email) {
            Mail::to($email)->queue(new NewsletterMail($this->newsletter->subject, $this->newsletter->body));
        }
    }
}
