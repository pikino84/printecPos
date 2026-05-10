<?php

namespace App\Mail;

use App\Models\Partner;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProfileReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Partner $partner,
        public int $daysRemaining,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->daysRemaining > 3
            ? 'Recordatorio: completa tu perfil en Printec'
            : "Te quedan {$this->daysRemaining} días para completar tu perfil";

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.profile-reminder',
            with: [
                'partner' => $this->partner,
                'daysRemaining' => $this->daysRemaining,
                'percentage' => $this->partner->profileCompletionPercentage(),
                'missing' => $this->partner->missingProfileFields(),
            ],
        );
    }
}
