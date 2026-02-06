<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->index('board_id');
            $table->index(['board_id', 'status']); // Compound index for board view
            $table->index('created_at'); // For sorting
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['board_id']);
            $table->dropIndex(['board_id', 'status']);
            $table->dropIndex(['created_at']);
        });
    }
};
