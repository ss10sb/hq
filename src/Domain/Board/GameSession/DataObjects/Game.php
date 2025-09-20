<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\DataObjects;

use Domain\Board\GameSession\Constants\Status;
use Domain\Board\GameSession\Contracts\Models\Game as GameModel;
use Domain\Shared\DataObjects\Player;
use Illuminate\Support\Collection;
use Smorken\Domain\DataObjects\DataObject;

final class Game extends DataObject
{
    /**
     * @var \Illuminate\Support\Collection<Player>
     */
    public Collection $players;

    public function __construct(
        public readonly int $id,
        public readonly string $joinKey,
        public readonly Status $status,
        public readonly int $gameMasterId,
        public readonly int $maxHeroes,
        public readonly int $currentHeroId,
        public readonly Heroes $heroes,
        iterable $players = [],
    ) {
        $this->players = new Collection;
        $this->ensurePlayers($players);
    }

    public static function fromGameModel(GameModel $gameModel): self
    {
        return new self(
            id: $gameModel->id,
            joinKey: $gameModel->join_key,
            status: $gameModel->status,
            gameMasterId: $gameModel->game_master_id,
            maxHeroes: $gameModel->max_heroes,
            currentHeroId: $gameModel->current_hero_id,
            heroes: $gameModel->heroes,
            players: $gameModel->users,
        );
    }

    public function hasPlayerByUserId(int $userId): bool
    {
        foreach ($this->players as $player) {
            if ($player->id === $userId) {
                return true;
            }
        }

        return false;
    }

    public function userIsAllowed(int $userId): bool
    {
        if ($this->gameMasterId === $userId) {
            return true;
        }

        return $this->hasPlayerByUserId($userId);
    }

    protected function ensurePlayers(iterable $players): void
    {
        foreach ($players as $player) {
            $this->players->push(Player::from($player));
        }
    }
}
