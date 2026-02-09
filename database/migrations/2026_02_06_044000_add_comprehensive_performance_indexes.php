<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Performance optimization indexes for all tables
     */
    public function up(): void
    {
        // Tasks table - additional performance indexes
        Schema::table('tasks', function (Blueprint $table) {
            // assigned_to and created_by already have indexes from previous migrations
            // We only add indexes that are truly new
            
            // We'll only add the ones that are definitely missing.
            if (!$this->indexExists('tasks', 'tasks_priority_index')) {
                $table->index('priority');
            }
            if (!$this->indexExists('tasks', 'tasks_deadline_index')) {
                $table->index('deadline');
            }
            if (!$this->indexExists('tasks', 'tasks_status_priority_index')) {
                $table->index(['status', 'priority']);
            }
        });

        // Subtasks table indexes - check if table exists first
        if (Schema::hasTable('subtasks')) {
            Schema::table('subtasks', function (Blueprint $table) {
                if (!$this->indexExists('subtasks', 'subtasks_task_id_index')) {
                    $table->index('task_id');
                }
                if (!$this->indexExists('subtasks', 'subtasks_task_id_status_index')) {
                    $table->index(['task_id', 'status']);
                }
            });
        }

        // Workspace members pivot table
        if (Schema::hasTable('workspace_user')) {
            Schema::table('workspace_user', function (Blueprint $table) {
                // Compound index for quick lookups - this one might be new
                if (!$this->indexExists('workspace_user', 'workspace_user_workspace_id_user_id_index')) {
                    $table->index(['workspace_id', 'user_id']);
                }
            });
        }

        // Activity logs
        if (Schema::hasTable('activity_logs')) {
            Schema::table('activity_logs', function (Blueprint $table) {
                if (!$this->indexExists('activity_logs', 'activity_logs_task_id_index')) {
                    $table->index('task_id');
                }
                if (!$this->indexExists('activity_logs', 'activity_logs_created_at_index')) {
                    $table->index('created_at');
                }
                if (!$this->indexExists('activity_logs', 'activity_logs_task_id_created_at_index')) {
                    $table->index(['task_id', 'created_at']);
                }
            });
        }
    }

    /**
     * Helper to check if an index exists
     */
    private function indexExists(string $table, string $index): bool
    {
        $conn = Schema::getConnection();
        $dbName = $conn->getDatabaseName();
        
        // This works for MySQL/MariaDB
        try {
            $results = DB::select("
                SELECT COUNT(1) as has_index
                FROM information_schema.statistics 
                WHERE table_schema = ? 
                AND table_name = ? 
                AND index_name = ?
            ", [$dbName, $table, $index]);

            return $results[0]->has_index > 0;
        } catch (\Exception $e) {
            // Fallback for SQLite or other DBs if needed
            return false;
        }
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if ($this->indexExists('tasks', 'tasks_priority_index')) $table->dropIndex(['priority']);
            if ($this->indexExists('tasks', 'tasks_deadline_index')) $table->dropIndex(['deadline']);
            if ($this->indexExists('tasks', 'tasks_status_priority_index')) $table->dropIndex(['status', 'priority']);
        });

        if (Schema::hasTable('subtasks')) {
            Schema::table('subtasks', function (Blueprint $table) {
                if ($this->indexExists('subtasks', 'subtasks_task_id_index')) $table->dropIndex(['task_id']);
                if ($this->indexExists('subtasks', 'subtasks_task_id_status_index')) $table->dropIndex(['task_id', 'status']);
            });
        }

        if (Schema::hasTable('workspace_user')) {
            Schema::table('workspace_user', function (Blueprint $table) {
                if ($this->indexExists('workspace_user', 'workspace_user_workspace_id_user_id_index')) {
                    $table->dropIndex(['workspace_id', 'user_id']);
                }
            });
        }

        if (Schema::hasTable('activity_logs')) {
            Schema::table('activity_logs', function (Blueprint $table) {
                if ($this->indexExists('activity_logs', 'activity_logs_task_id_index')) $table->dropIndex(['task_id']);
                if ($this->indexExists('activity_logs', 'activity_logs_created_at_index')) $table->dropIndex(['created_at']);
                if ($this->indexExists('activity_logs', 'activity_logs_task_id_created_at_index')) $table->dropIndex(['task_id', 'created_at']);
            });
        }
    }
};
