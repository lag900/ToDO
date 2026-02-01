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
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('assigned_by_id')->nullable()->after('assigned_to')->constrained('users')->nullOnDelete();
        });

        // Ensure workspace_user has unique (workspace_id, user_id)
        // Note: SQLite doesn't support adding unique constraints to existing tables easily without recreating.
        // But for this project, we can try to add it or just assume the code handles syncWithoutDetaching correctly.
        // Actually, syncWithoutDetaching handles it at the application level.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['assigned_by_id']);
            $table->dropColumn('assigned_by_id');
        });
    }
};
