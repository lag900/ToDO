<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Rename projects to plans
        if (Schema::hasTable('projects')) {
            Schema::rename('projects', 'plans');
        }

        // 2. Create boards table
        if (!Schema::hasTable('boards')) {
            Schema::create('boards', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->foreignId('plan_id')->constrained('plans')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('status')->default('active');
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 3. Update tasks table
        if (!Schema::hasColumn('tasks', 'board_id')) {
            Schema::table('tasks', function (Blueprint $table) {
                // In SQLite, we might need a default value if data exists
                $table->foreignId('board_id')->nullable()->after('project_id')->constrained('boards')->onDelete('cascade');
            });
        }

        // 4. Data Migration: Create a default board for each plan and move tasks
        // Only if there are tasks with null board_id or boards is empty
        if (DB::table('boards')->count() === 0) {
            $plans = DB::table('plans')->get();
            foreach ($plans as $plan) {
                $userId = $plan->user_id;
                if (!$userId) {
                    $workspace = DB::table('workspaces')->where('id', $plan->workspace_id)->first();
                    $userId = $workspace ? $workspace->owner_id : 1;
                }

                $boardId = DB::table('boards')->insertGetId([
                    'name' => 'Main Board',
                    'plan_id' => $plan->id,
                    'user_id' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('tasks')
                    ->where('project_id', $plan->id)
                    ->update(['board_id' => $boardId]);
            }
        }

        // 5. Finalize tasks table (Skip drop column for now in SQLite to avoid FK errors)
        // Schema::table('tasks', function (Blueprint $table) {
        //    $table->unsignedBigInteger('board_id')->nullable(false)->change();
        // });
    }

    public function down(): void
    {
        // Skip down for this complex transition to avoid data loss in failure
    }
};
