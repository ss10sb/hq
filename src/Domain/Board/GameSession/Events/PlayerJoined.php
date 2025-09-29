<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\Events;

use Domain\Shared\DataObjects\Player;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;

abstract class PlayerJoined implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets;

    protected string $broadcastAsKey;

    protected string $broadcastChannel;

    public function __construct(
        public readonly int $gameId,
        public readonly Player $player
    ) {}

    public function broadcastAs(): string
    {
        return $this->broadcastAsKey;
    }

    public function broadcastOn(): Channel
    {
        return new PresenceChannel($this->broadcastChannel.$this->gameId);
    }

    public function broadcastWith(): array
    {
        return [
            'player' => $this->serializePlayer($this->player),
        ];
    }

    /**
     * @return array{id:int,name:string}
     */
    protected function serializePlayer(Player $player): array
    {
        return $player->toArray();
    }
}
