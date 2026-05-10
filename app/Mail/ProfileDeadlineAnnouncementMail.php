<?php

namespace App\Mail;

use App\Models\Partner;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProfileDeadlineAnnouncementMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Partner $partner) {}

    public function envelope(): Envelope
    {
        $deadline = $this->partner->profile_deadline_at?->translatedFormat('j \d\e F \d\e Y');

        return new Envelope(
            subject: "Acción requerida: completa tu perfil antes del {$deadline}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.profile-deadline-announcement',
            with: [
                'partner' => $this->partner,
                'percentage' => $this->partner->profileCompletionPercentage(),
                'missing' => $this->partner->missingProfileFields(),
                'daysRemaining' => $this->partner->daysUntilDeadline(),
                'deadlineFormatted' => $this->partner->profile_deadline_at?->translatedFormat('j \d\e F \d\e Y'),
            ],
        );
    }
}
