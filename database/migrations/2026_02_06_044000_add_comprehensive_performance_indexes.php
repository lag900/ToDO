<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Performance optimization indexes for all tables
     */
    public function up(): void
    {
        // Tasks table - additional performance indexes
        Schema::table('tasks', function (Blueprint $table) {
            $table->index('assigned_to'); // For filtering by assignee
            $table->index('created_by'); // For filtering by creator
            $table->index('priority'); // For priority filtering
            $table->index('deadline'); // For deadline sorting/filtering
            $table->index(['status', 'priority']); // Compound for status + priority views
        });

        // Subtasks table indexes
        Schema::table('subtasks', function (Blueprint $table) {
            $table->index('task_id'); // Foreign key index
            $table->index(['task_id', 'status']); // Compound for task subtasks view
        });

        // Boards table indexes
        Schema::table('boards', function (Blueprint $table) {
            $table->index('plan_id'); // Foreign key index
        });

        // Plans table indexes
        Schema::table('plans', function (Blueprint $table) {
            $table->index('workspace_id'); // Foreign key index
        });

        // Workspace members pivot table
        if (Schema::hasTable('workspace_user')) {
            Schema::table('workspace_user', function (Blueprint $table) {
                $table->index('workspace_id');
                $table->index('user_id');
                $table->index(['workspace_id', 'user_id']); // Compound for quick lookups
            });
        }

        // Activity logs (if exists)
        if (Schema::hasTable('activity_logs')) {
            Schema::table('activity_logs', function (Blueprint $table) {
                $table->index('task_id');
                $table->index('created_at'); // For chronological queries
                $table->index(['task_id', 'created_at']); // Compound for task history
            });
        }
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['assigned_to']);
            $table->dropIndex(['created_by']);
            $table->dropIndex(['priority']);
            $table->dropIndex(['deadline']);
            $table->dropIndex(['status', 'priority']);
        });

        Schema::table('subtasks', function (Blueprint $table) {
            $table->dropIndex(['task_id']);
            $table->dropIndex(['task_id', 'status']);
        });

        Schema::table('boards', function (Blueprint $table) {
            $table->dropIndex(['plan_id']);
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->dropIndex(['workspace_id']);
        });

        if (Schema::hasTable('workspace_user')) {
            Schema::table('workspace_user', function (Blueprint $table) {
                $table->dropIndex(['workspace_id']);
                $table->dropIndex(['user_id']);
                $table->dropIndex(['workspace_id', 'user_id']);
            });
        }

        if (Schema::hasTable('activity_logs')) {
            Schema::table('activity_logs', function (Blueprint $table) {
                $table->dropIndex(['task_id']);
                $table->dropIndex(['created_at']);
                $table->dropIndex(['task_id', 'created_at']);
            });
        }
    }
};
