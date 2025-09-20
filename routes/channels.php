<?php

use Domain\Board\GameSession\Broadcasting\GamePresenceChannel;
use Domain\Shared\Contracts\Models\User;
use Illuminate\Support\Facades\Broadcast;

// Presence channel for game
Broadcast::channel('game.waiting-room.{gameId}', GamePresenceChannel::class);
Broadcast::channel('game.play.{gameId}', GamePresenceChannel::class);
Broadcast::channel('game-access.{gameId}', function (User $user, int $gameId): bool {
    return true;
});
