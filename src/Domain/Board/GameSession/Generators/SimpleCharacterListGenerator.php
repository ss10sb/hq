<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\Generators;

use Domain\Board\GameSession\Contracts\Generators\JoinKeyGenerator;

class SimpleCharacterListGenerator implements JoinKeyGenerator
{
    protected string $characters;

    protected int $charactersLength;

    public function __construct()
    {
        $this->characters = '123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ';
        $this->charactersLength = strlen($this->characters);
    }

    public function __invoke(int $length = 6): string
    {
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $this->characters[random_int(0, $this->charactersLength - 1)];
        }

        return $randomString;
    }
}
