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
        // 1. Drop the index and foreign key
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex('tasks_status_project_id_index');
            // In SQLite, dropForeign might not work as expected via Blueprint, 
            // but let's try or use a raw statement to remove the FK constraint if possible.
            // Actually, in SQLite you can't just drop an FK. You must recreate the table.
        });

        // Since SQLite is being difficult, we will recreate the table manually or use a workaround.
        // The most compatible way to fix the "NOT NULL" error without dropping the column 
        // (since dropping is failing) is to make it nullable.
        
        // But Laravel's $table->unsignedBigInteger('project_id')->nullable()->change() 
        // also requires recreating the table.
        
        // Let's try the "Nullable" approach first, as it might bypass the FK validation issue 
        // if we keep the column but allow it to be empty.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
