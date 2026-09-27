<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** A newsletter issue, sent queued per recipient (the log driver locally). */
class NewsletterMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(private readonly string $subject_, private readonly string $body) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->subject_);
    }

    public function content(): Content
    {
        return new Content(text: 'mail.test', with: ['content' => $this->body]);
    }
}
