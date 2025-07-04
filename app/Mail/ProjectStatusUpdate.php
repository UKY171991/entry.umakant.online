<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProjectStatusUpdate extends Mailable
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
                'progressPercentage' => $args[2] ?? 50,
                'completedTasks' => $args[3] ?? 'Tasks completed so far',
                'upcomingTasks' => $args[4] ?? 'Upcoming milestones',
                'notes' => $args[5] ?? ''
            ];
        }
    }

    public function envelope(): Envelope
    {
        $projectName = $this->data['projectName'] ?? 'Your Project';
        $progressPercentage = $this->data['progressPercentage'] ?? 50;
        return new Envelope(
            subject: 'Project Update: ' . $projectName . ' - ' . $progressPercentage . '% Complete',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.templates.project_status_update',
            with: $this->data,
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
