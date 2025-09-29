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
            $table->foreignId('creator_id')->constrained('users')->noActionOnDelete();
            $table->unsignedTinyInteger('width');
            $table->unsignedTinyInteger('height');
            $table->json('tiles');
            $table->json('elements');
            $table->boolean('is_public');
            $table->timestamps();

            $table->index('group');
            $table->index('order');
            $table->index('creator_id');
            $table->index('is_public');
        });

        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('board_id')->constrained('boards')->cascadeOnDelete();
            $table->string('join_key', 16);
            $table->foreignId('game_master_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 16);
            $table->unsignedTinyInteger('max_heroes')->default(4);
            $table->json('elements');
            $table->json('tiles');
            $table->unsignedBigInteger('current_hero_id')->default(0);
            $table->timestamps();

            $table->index('join_key');
            $table->index('game_master_id');
            $table->index('status');
        });

        Schema::create('heroes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 32);
            $table->string('type', 16);
            $table->json('stats');
            $table->json('equipment');
            $table->json('inventory');
            $table->unsignedMediumInteger('gold')->default(0);
            $table->timestamps();
        });

        Schema::create('game_hero', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('games')->cascadeOnDelete();
            $table->unsignedBigInteger('hero_id')->default(0);
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('order')->default(0);
            $table->unsignedTinyInteger('body_points')->default(0);
            $table->mediumInteger('x')->default(0);
            $table->mediumInteger('y')->default(0);
            $table->timestamps();

            $table->unique(['game_id', 'hero_id']);
        });

        Schema::create('game_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('games')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boards');
        Schema::dropIfExists('games');
        Schema::dropIfExists('heroes');
        Schema::dropIfExists('game_user');
    }
};
