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
        Schema::create('boards', function (Blueprint $table) {
            $table->id();
            $table->string('name', 128);
            $table->string('group')->nullable()->default(null);
            $table->unsignedTinyInteger('order')->default(0);
            $table->foreignId('creator_id')->constrained('users');
            $table->unsignedTinyInteger('width');
            $table->unsignedTinyInteger('height');
            $table->json('tiles');
            $table->boolean('is_public');
            $table->timestamps();

            $table->index('group');
            $table->index('order');
            $table->index('creator_id');
            $table->index('is_public');
        });

        Schema::create('board_elements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_id')->constrained('boards');
            $table->string('ui_id');
            $table->string('name', 128);
            $table->string('type', 16);
            $table->unsignedMediumInteger('x');
            $table->unsignedMediumInteger('y');
            $table->json('properties');
            $table->json('stats')->nullable()->default(null);
            $table->timestamps();

            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boards');
        Schema::dropIfExists('board_elements');
    }
};
