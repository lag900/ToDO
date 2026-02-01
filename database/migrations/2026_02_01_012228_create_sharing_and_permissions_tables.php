<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Plan User Pivot Table for granular sharing
        Schema::create('plan_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('plans')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('role')->default('viewer'); // owner, editor, viewer
            $table->timestamps();
            
            $table->unique(['plan_id', 'user_id']);
        });

        // 2. Invitations for people not yet in the system (or not yet in the workspace)
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->string('inviteable_type'); // Workspace or Plan
            $table->unsignedBigInteger('inviteable_id');
            $table->string('role')->default('viewer');
            $table->foreignId('invited_by')->constrained('users')->onDelete('cascade');
            $table->string('status')->default('pending'); // pending, accepted
            $table->timestamps();
            
            $table->index(['inviteable_type', 'inviteable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_user');
        Schema::dropIfExists('invitations');
    }
};
