<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendWebsiteDevelopmentUpdates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-website-development-updates';

    protected $description = 'Send weekly website development updates to clients.';

    public function handle()
    {
        $this->info('Sending weekly website development updates...');

        $clients = \App\Models\Client::all(); // Or filter based on specific criteria

        if ($clients->isEmpty()) {
            $this->info('No clients found to send updates to.');
            return;
        }

        foreach ($clients as $client) {
            try {
                // Customize subject and body based on client or project data
                $subject = 'Weekly Website Development Update for ' . $client->company_name;
                $body = 'Dear ' . $client->contact_person . ',

This is your weekly update on the progress of your website development project. We are currently working on [specific tasks/milestones].

[Add more details about progress, next steps, or any requests for client input.]

Best regards,
Your Development Team';

                // Ensure the client has an email address
                if ($client->email) {
                    \Illuminate\Support\Facades\Mail::to($client->email)->send(new \App\Mail\WebsiteDevelopmentUpdate($subject, $body));
                    $this->info('Update sent to client: ' . $client->company_name . ' (' . $client->email . ')');
                } else {
                    $this->warn('Client ' . $client->company_name . ' does not have an email address. Skipping.');
                }
            } catch (\Exception $e) {
                $this->error('Failed to send update to client: ' . $client->company_name . ' - ' . $e->getMessage());
            }
        }

        $this->info('Weekly website development updates sent successfully!');
    }

    


}
