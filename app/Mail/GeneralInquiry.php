<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GeneralInquiry extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    public function __construct($data)
    {
        $this->data = is_array($data) ? $data : ['clientName' => $data];
    }

    public function envelope(): Envelope
    {
        $clientName = $this->data['clientName'] ?? 'Valued Client';
        return new Envelope(
            subject: 'Thank You for Your Inquiry - ' . $clientName,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.templates.general_inquiry',
            with: $this->data,
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
