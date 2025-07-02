<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrate emails table: match client_name to client.name and set client_id
        $emails = DB::table('emails')->whereNotNull('client_name')->get();
        foreach ($emails as $email) {
            $client = DB::table('clients')->where('name', $email->client_name)->first();
            if ($client) {
                DB::table('emails')
                    ->where('id', $email->id)
                    ->update(['client_id' => $client->id]);
            }
        }

        // Migrate websites table: match client_name to client.name and set client_id
        $websites = DB::table('websites')->whereNotNull('client_name')->get();
        foreach ($websites as $website) {
            $client = DB::table('clients')->where('name', $website->client_name)->first();
            if ($client) {
                DB::table('websites')
                    ->where('id', $website->id)
                    ->update(['client_id' => $client->id]);
            }
        }

        // Migrate pending_tasks table: match client_name to client.name and set client_id
        $tasks = DB::table('pending_tasks')->whereNotNull('client_name')->get();
        foreach ($tasks as $task) {
            $client = DB::table('clients')->where('name', $task->client_name)->first();
            if ($client) {
                DB::table('pending_tasks')
                    ->where('id', $task->id)
                    ->update(['client_id' => $client->id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse migration: set client_name back from client.name where client_id is set
        $emails = DB::table('emails')->whereNotNull('client_id')->get();
        foreach ($emails as $email) {
            $client = DB::table('clients')->where('id', $email->client_id)->first();
            if ($client) {
                DB::table('emails')
                    ->where('id', $email->id)
                    ->update(['client_name' => $client->name]);
            }
        }

        $websites = DB::table('websites')->whereNotNull('client_id')->get();
        foreach ($websites as $website) {
            $client = DB::table('clients')->where('id', $website->client_id)->first();
            if ($client) {
                DB::table('websites')
                    ->where('id', $website->id)
                    ->update(['client_name' => $client->name]);
            }
        }

        $tasks = DB::table('pending_tasks')->whereNotNull('client_id')->get();
        foreach ($tasks as $task) {
            $client = DB::table('clients')->where('id', $task->client_id)->first();
            if ($client) {
                DB::table('pending_tasks')
                    ->where('id', $task->id)
                    ->update(['client_name' => $client->name]);
            }
        }
    }
};
