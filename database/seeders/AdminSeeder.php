<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * The local development admin. Never run against production: the password is
 * published in the README on purpose, because it only ever guards a laptop.
 */
class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrNew(['email' => 'admin@wowmart.test']);
        $admin->fill(['name' => 'WowMart Admin', 'password' => 'password']);

        // Not mass-assignable by design, so it is set explicitly - never from input.
        $admin->user_type = UserType::Admin;
        $admin->save();

        $this->command?->info('Admin: admin@wowmart.test / password');
    }
}
