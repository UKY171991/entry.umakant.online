<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendPendingTaskReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-pending-task-reminders';

    protected $description = 'Send email reminders for pending tasks.';

    public function handle()
    {
        $this->info('Sending pending task reminders...');

        $pendingTasks = \App\Models\PendingTask::where('status', 'Pending')->get();

        if ($pendingTasks->isEmpty()) {
            $this->info('No pending tasks found.');
            return;
        }

        foreach ($pendingTasks as $task) {
            try {
                $subject = 'Reminder: Pending Task - ' . $task->task_name;
                $body = 'This is a reminder for your pending task: ' . $task->task_name . '.\n\nDescription: ' . $task->description . '\nDue Date: ' . $task->due_date->format('Y-m-d');
                
                // Assuming you want to send to a specific email address, e.g., uky171991@gmail.com
                // You might want to make this configurable or fetch it from a user associated with the task
                \Illuminate\Support\Facades\Mail::to('uky171991@gmail.com')->send(new \App\Mail\PendingTaskReminder($subject, $body));
                
                $this->info('Reminder sent for task: ' . $task->task_name);
            } catch (\Exception $e) {
                $this->error('Failed to send reminder for task: ' . $task->task_name . ' - ' . $e->getMessage());
            }
        }

        $this->info('Pending task reminders sent successfully!');
    }
}
