<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\Exceptions;

class HeroNotAllowedException extends GameException
{
    public static function make(): self
    {
        return new self('You do not have permission to play this hero.');
    }
}
