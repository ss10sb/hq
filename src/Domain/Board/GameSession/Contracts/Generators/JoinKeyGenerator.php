<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\Contracts\Generators;

interface JoinKeyGenerator
{
    public function __invoke(int $length = 6): string;
}
