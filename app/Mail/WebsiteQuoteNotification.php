<?php

namespace App\Mail;

use App\Models\Quote;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WebsiteQuoteNotification extends Mailable
{
    use Queueable, SerializesModels;

    public Quote $quote;

    public function __construct(Quote $quote)
    {
        $this->quote = $quote;
    }

    public function build()
    {
        $this->quote->load(['items.variant.product', 'client']);

        return $this->subject("Nueva solicitud de cotización desde el sitio web - {$this->quote->quote_number}")
            ->view('emails.website-quote');
    }
}
