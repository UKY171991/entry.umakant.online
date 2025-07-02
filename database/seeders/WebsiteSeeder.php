<?php

namespace Database\Seeders;

use App\Models\Website;
use Illuminate\Database\Seeder;

class WebsiteSeeder extends Seeder
{
    public function run(): void
    {
        $websites = [
            ['client_name' => 'Vishal Sir', 'website_url' => 'https://vascularlimbsalvage.com/', 'status' => 'N/A', 'last_updated' => '2025-04-06 10:00:01'],
            ['client_name' => 'Vishal Sir', 'website_url' => 'https://365hiresolutions.com/', 'status' => 'N/A', 'last_updated' => '2025-04-06 10:00:22'],
            ['client_name' => 'Vishal Sir', 'website_url' => 'https://callidora.in/', 'status' => 'UP', 'last_updated' => '2025-04-06 10:00:17'],
            ['client_name' => 'Vishal Sir', 'website_url' => 'https://venous.in/', 'status' => 'N/A', 'last_updated' => '2025-04-06 10:00:12'],
            ['client_name' => 'Vishal Sir', 'website_url' => 'https://physiosynapse.com/', 'status' => 'N/A', 'last_updated' => '2025-04-06 10:00:11'],
            ['client_name' => 'Vishal Sir', 'website_url' => 'https://leventor.com/', 'status' => 'N/A', 'last_updated' => '2025-04-06 10:00:11'],
            ['client_name' => 'Vishal Sir', 'website_url' => 'https://drjayakrishna.in/', 'status' => 'N/A', 'last_updated' => '2025-04-06 10:00:11'],
        ];

        foreach ($websites as $website) {
            Website::create($website);
        }
    }
}
