<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WebsiteProposal extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    public function __construct($data)
    {
        // Handle both array and individual parameters for backward compatibility
        if (is_array($data)) {
            $this->data = $data;
        } else {
            // Legacy support - if called with individual parameters
            $args = func_get_args();
            $this->data = [
                'clientName' => $args[0] ?? 'Valued Client',
                'projectName' => $args[1] ?? '',
                'estimatedCost' => $args[2] ?? 0,
                'timeframe' => $args[3] ?? '',
                'notes' => $args[4] ?? ''
            ];
        }
    }

    public function envelope(): Envelope
    {
        $clientName = $this->data['clientName'] ?? 'Valued Client';
        return new Envelope(
            subject: 'Website Development Proposal - ' . $clientName,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.templates.website_proposal',
            with: $this->data,
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
