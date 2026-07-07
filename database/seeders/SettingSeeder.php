<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::updateOrCreate(
            ['key' => 'welcome_email_subject'],
            ['value' => 'Welcome to Abhaya Vastra!']
        );
        Setting::updateOrCreate(
            ['key' => 'welcome_email_body'],
            ['value' => "Hi {name},\n\nThank you for registering at Abhaya Vastra. We are excited to have you onboard!\n\nBest regards,\nAbhaya Vastra Team"]
        );
        Setting::updateOrCreate(
            ['key' => 'order_status_email_subject'],
            ['value' => 'Update: Order #{order_number} status changed']
        );
        Setting::updateOrCreate(
            ['key' => 'order_status_email_body'],
            ['value' => "Hi {name},\n\nYour order #{order_number} status has been updated to: {status}.\n\nThank you for shopping with us!\n\nBest regards,\nAbhaya Vastra Team"]
        );
    }
}
