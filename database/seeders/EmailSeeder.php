<?php

namespace Database\Seeders;

use App\Models\Email;
use Illuminate\Database\Seeder;

class EmailSeeder extends Seeder
{
    public function run(): void
    {
        $emails = [
            ['client_name' => 'guy@guyyarkoni.com', 'email' => 'guy@guyyarkoni.com'],
            ['client_name' => 'sales@eonreality.com', 'email' => 'sales@eonreality.com'],
            ['client_name' => 'info@vezlon.com', 'email' => 'info@vezlon.com'],
            ['client_name' => 'weddings@fearrington.com', 'email' => 'weddings@fearrington.com'],
            ['client_name' => 'homes@fearrington.com', 'email' => 'homes@fearrington.com'],
            ['client_name' => 'fhouse@fearrington.com', 'email' => 'fhouse@fearrington.com'],
        ];

        foreach ($emails as $email) {
            Email::create($email);
        }
    }
}
