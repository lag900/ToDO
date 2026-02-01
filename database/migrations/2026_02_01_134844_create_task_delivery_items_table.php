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
        Schema::create('task_delivery_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_delivery_id')->constrained()->onDelete('cascade');
            $table->string('type'); // file, image, link
            $table->string('name');
            $table->text('content'); // URL or File Path
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_delivery_items');
    }
};
