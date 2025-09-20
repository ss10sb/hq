<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\Events;

final class PlayerJoinedWaitingRoom extends PlayerJoined
{
    protected string $broadcastAsKey = 'player.joined.waiting-room';

    protected string $broadcastChannel = 'game.waiting-room.';
}
