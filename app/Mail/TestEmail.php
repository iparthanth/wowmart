<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** The "send test email" action on the SMTP settings screen. */
class TestEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(private readonly string $content) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'WowMart SMTP test');
    }

    public function content(): Content
    {
        return new Content(text: 'mail.test', with: ['content' => $this->content]);
    }
}
