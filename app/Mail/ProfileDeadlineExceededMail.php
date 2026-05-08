<?php

namespace App\Mail;

use App\Models\Partner;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProfileDeadlineExceededMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Partner $partner) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu cuenta ha sido suspendida — perfil no completado a tiempo',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.profile-deadline-exceeded',
            with: [
                'partner' => $this->partner,
                'vetoedUntil' => $this->partner->vetoed_until,
            ],
        );
    }
}
