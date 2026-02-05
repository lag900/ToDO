<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@todo.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
            ]
        );

        $roles = ['Admin', 'Manager', 'Team Leader', 'Employee'];
        foreach ($roles as $role) {
            \App\Models\Role::firstOrCreate(
                ['slug' => strtolower(str_replace(' ', '_', $role))],
                ['name' => $role]
            );
        }

        // Create Default Workspace
        $workspace = \App\Models\Workspace::firstOrCreate(
            ['owner_id' => $admin->id, 'name' => 'Main Workspace'],
            [
                'type' => 'company',
                'intent' => 'organization',
                'settings' => ['theme' => 'light']
            ]
        );

        // Attach admin to workspace as member/owner
        $workspace->members()->syncWithoutDetaching([
            $admin->id => ['role' => 'owner', 'status' => 'active']
        ]);

        // Create Default Plan
        $plan = \App\Models\Plan::firstOrCreate(
            ['workspace_id' => $workspace->id, 'name' => 'Main Plan'],
            [
                'description' => 'The primary company plan',
                'status' => 'active',
                'user_id' => $admin->id
            ]
        );

        // Create Default Board
        \App\Models\Board::firstOrCreate(
            ['plan_id' => $plan->id, 'name' => 'General Board'],
            [
                'description' => 'Main task board',
                'status' => 'active',
                'user_id' => $admin->id
            ]
        );
    }
}
