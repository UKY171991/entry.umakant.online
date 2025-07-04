<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProjectCompletion extends Mailable
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
                'projectName' => $args[1] ?? 'Your Project',
                'websiteUrl' => $args[2] ?? 'https://example.com',
                'loginCredentials' => $args[3] ?? [],
                'supportDetails' => $args[4] ?? ''
            ];
        }
    }

    public function envelope(): Envelope
    {
        $projectName = $this->data['projectName'] ?? 'Your Project';
        return new Envelope(
            subject: 'Project Completed: ' . $projectName . ' is Live!',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.templates.project_completion',
            with: $this->data,
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
