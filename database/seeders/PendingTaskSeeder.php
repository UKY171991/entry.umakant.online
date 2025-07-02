<?php

namespace Database\Seeders;

use App\Models\PendingTask;
use Illuminate\Database\Seeder;

class PendingTaskSeeder extends Seeder
{
    public function run(): void
    {
        $tasks = [
            [
                'task_name' => 'Dilmog.com',
                'client_name' => 'Zidpro',
                'description' => 'The logo for login page will be from Dark Logo',
                'due_date' => '2025-06-05',
                'status' => 'Completed',
                'payment' => 0.00,
                'payment_status' => 'Unpaid',
            ],
            [
                'task_name' => 'Dilmog.com',
                'client_name' => 'Zidpro',
                'description' => 'See what I asked you to do... On mobile the field should be horizontally aligned with the logo like in Netflix mobile',
                'due_date' => '2025-06-05',
                'status' => 'Completed',
                'payment' => 0.00,
                'payment_status' => 'Unpaid',
            ],
            [
                'task_name' => 'Dilmog.com',
                'client_name' => 'Zidpro',
                'description' => 'The logo for login page will be from Dark Logo',
                'due_date' => '2025-06-05',
                'status' => 'Completed',
                'payment' => 0.00,
                'payment_status' => 'Unpaid',
            ],
            [
                'task_name' => 'Dilmog.com',
                'client_name' => 'Zidpro',
                'description' => 'Reduce spacing to be like login page own',
                'due_date' => '2025-06-05',
                'status' => 'Completed',
                'payment' => 0.00,
                'payment_status' => 'Unpaid',
            ],
            [
                'task_name' => 'Dilmog.com',
                'client_name' => 'Zidpro',
                'description' => 'Make the register like this.... like thisogin page better',
                'due_date' => '2025-06-05',
                'status' => 'Completed',
                'payment' => 0.00,
                'payment_status' => 'Unpaid',
            ],
            [
                'task_name' => 'Doctor',
                'client_name' => 'Gyas',
                'description' => 'Doctor CRM',
                'due_date' => '2025-06-28',
                'status' => 'Pending',
                'payment' => 0.00,
                'payment_status' => 'Unpaid',
            ],
        ];

        foreach ($tasks as $task) {
            PendingTask::create($task);
        }
    }
}
