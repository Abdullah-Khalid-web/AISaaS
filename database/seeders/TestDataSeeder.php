<?php
// database/seeders/TestDataSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\AiTool;
use App\Models\Plan;
use App\Models\License;
use App\Models\Payment;
use App\Models\UsageLog;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TestDataSeeder extends Seeder
{
    public function run()
    {
        // Create test user if not exists
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'email_verified_at' => now()
            ]
        );

        // Create test tool
        $tool = AiTool::firstOrCreate(
            ['slug' => 'test-tool'],
            [
                'name' => 'Test Tool',
                'description' => 'A test tool for development',
                'is_active' => true,
                'version' => '1.0.0',
                'supported_platforms' => ['web']
            ]
        );

        // Create test plan
        $plan = Plan::firstOrCreate(
            ['slug' => 'test-plan'],
            [
                'tool_id' => $tool->id,
                'name' => 'Test Plan',
                'price' => 9.99,
                'currency' => 'USD',
                'billing_cycle' => 'monthly',
                'duration_days' => 30,
                'device_limit' => 1,
                'api_call_limit' => 100,
                'is_active' => true
            ]
        );

        // Create test license
        $license = License::firstOrCreate(
            ['license_key' => 'TEST-' . Str::random(8)],
            [
                'user_id' => $user->id,
                'tool_id' => $tool->id,
                'plan_id' => $plan->id,
                'package_name' => 'com.test.app',
                'status' => 'active',
                'expires_at' => Carbon::now()->addDays(30),
                'api_calls_used' => 0,
                'api_calls_limit' => 100
            ]
        );

        // Create some usage logs
        for ($i = 0; $i < 10; $i++) {
            UsageLog::create([
                'license_id' => $license->id,
                'user_id' => $user->id,
                'event_type' => 'api_call',
                'event_name' => 'test_generation',
                'was_successful' => rand(0, 10) > 2, // 80% success rate
                'response_time_ms' => rand(100, 500),
                'created_at' => Carbon::now()->subHours(rand(1, 48))
            ]);
        }

        $this->command->info('Test data created successfully!');
    }
}
