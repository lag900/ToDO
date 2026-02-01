<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->string('user_email')->nullable()->after('user_id');
            $table->string('task_name')->nullable()->after('task_id');
            $table->foreignId('workspace_id')->nullable()->after('changes')->constrained()->onDelete('cascade');
            
            // In Laravel 11/12 with SQLite, we might need to be careful with dropping FKs.
            // But we can at least make the columns nullable to prepare for set null if we were on MySQL.
            // For this task, snapping the names/emails is the priority.
            $table->unsignedBigInteger('task_id')->nullable()->change();
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropForeign(['workspace_id']);
            $table->dropColumn(['user_email', 'task_name', 'workspace_id']);
        });
    }
};
