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
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->string('join_key', 16);
            $table->string('name');
            $table->string('description')->nullable()->default(null);
            $table->foreignId('game_master_id')->constrained('users');
            $table->string('status', 16);
            $table->unsignedTinyInteger('max_players');
            $table->json('settings');
            $table->timestamps();

            $table->index('join_key');
            $table->index('status');
        });

        Schema::create('characters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('class', 16);
            $table->string('name', 32);
            $table->string('archetype', 16);
            $table->json('stats');
            $table->json('equipment');
            $table->json('inventory');
            $table->timestamps();
        });

        Schema::create('game_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('games');
            $table->foreignId('character_id')->constrained('characters');
            $table->foreignId('user_id')->constrained('users');
            $table->timestamp('joined_at');
        });

        Schema::create('game_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_id')->constrained('games');
            $table->json('board_state');
            $table->json('game_state');
            $table->json('turn_order');
            $table->foreignId('current_participant_id')->constrained('game_participants');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
        Schema::dropIfExists('characters');
        Schema::dropIfExists('game_participants');
        Schema::dropIfExists('game_sessions');
    }
};
