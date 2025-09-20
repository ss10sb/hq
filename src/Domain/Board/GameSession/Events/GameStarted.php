<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

final class GameStarted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(public readonly int $gameId) {}

    public function broadcastAs(): string
    {
        return 'game.started';
    }

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('game-access.'.$this->gameId);
    }
}
