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
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@todo.com',
            'password' => bcrypt('password'),
        ]);

        \App\Models\Project::create([
            'name' => 'Main Project',
            'description' => 'The primary company project',
            'status' => 'active',
        ]);

        $roles = ['Admin', 'Manager', 'Team Leader', 'Employee'];
        foreach ($roles as $role) {
            \App\Models\Role::create([
                'name' => $role,
                'slug' => strtolower(str_replace(' ', '_', $role)),
            ]);
        }
    }
}
