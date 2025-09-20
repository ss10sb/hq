<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\Events;

final class PlayerJoinedGame extends PlayerJoined
{
    protected string $broadcastAsKey = 'player.joined.game';

    protected string $broadcastChannel = 'game.play.';
}
