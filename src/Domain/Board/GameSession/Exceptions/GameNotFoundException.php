<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\Exceptions;

class GameNotFoundException extends GameException
{
    public static function make(): self
    {
        return new self('Game not found or is not available.');
    }
}
