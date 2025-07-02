<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

use App\Models\Client;
use App\Models\Email;
use App\Models\Website;
use App\Models\PendingTask;

echo "Starting client data migration...\n";

// Get all clients for mapping
$clients = Client::all()->keyBy('name');
echo "Found " . $clients->count() . " clients\n";

// Migrate Emails
echo "\nMigrating Emails...\n";
$emails = Email::whereNull('client_id')->get();
foreach ($emails as $email) {
    if ($email->client_name && isset($clients[$email->client_name])) {
        $email->client_id = $clients[$email->client_name]->id;
        $email->save();
        echo "Updated email ID {$email->id} - Client: {$email->client_name} -> ID: {$email->client_id}\n";
    } else {
        echo "Warning: Email ID {$email->id} has unknown client: '{$email->client_name}'\n";
    }
}

// Migrate Websites
echo "\nMigrating Websites...\n";
$websites = Website::whereNull('client_id')->get();
foreach ($websites as $website) {
    if ($website->client_name && isset($clients[$website->client_name])) {
        $website->client_id = $clients[$website->client_name]->id;
        $website->save();
        echo "Updated website ID {$website->id} - Client: {$website->client_name} -> ID: {$website->client_id}\n";
    } else {
        echo "Warning: Website ID {$website->id} has unknown client: '{$website->client_name}'\n";
    }
}

// Migrate Pending Tasks
echo "\nMigrating Pending Tasks...\n";
$tasks = PendingTask::whereNull('client_id')->get();
foreach ($tasks as $task) {
    if ($task->client_name && isset($clients[$task->client_name])) {
        $task->client_id = $clients[$task->client_name]->id;
        $task->save();
        echo "Updated task ID {$task->id} - Client: {$task->client_name} -> ID: {$task->client_id}\n";
    } else {
        echo "Warning: Task ID {$task->id} has unknown client: '{$task->client_name}'\n";
    }
}

echo "\nMigration completed!\n";
