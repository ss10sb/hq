<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

final class GameHeroesUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    /**
     * @param  array<int, array<string, mixed>>  $heroes
     */
    public function __construct(
        public readonly int $gameId,
        public readonly array $heroes,
    ) {}

    public function broadcastAs(): string
    {
        return 'game.heroes.updated';
    }

    public function broadcastOn(): Channel
    {
        return new PresenceChannel('game.play.'.$this->gameId);
    }

    public function broadcastWith(): array
    {
        return [
            'heroes' => $this->heroes,
        ];
    }
}
