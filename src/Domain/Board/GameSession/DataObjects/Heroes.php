<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\DataObjects;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Smorken\Domain\DataObjects\DataObject;

/**
 * @method bool isEmpty()
 * @method bool isNotEmpty()
 */
final class Heroes extends DataObject
{
    /**
     * @var \Illuminate\Support\Collection<\Domain\Board\Elements\Heros\Contracts\Models\Hero|\Domain\Board\GameSession\DataObjects\Zargon>
     */
    public Collection $heroes;

    public function __construct(
        iterable $heroes = [],
    ) {
        $this->heroes = new Collection;
        $this->ensureHeroes($heroes);
    }

    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    public static function fromJson(string $data): self
    {
        return self::fromArray(json_decode($data, true));
    }

    public static function fromRequest(Request $request): self
    {
        return self::fromArray($request->input('heroes') ?? []);
    }

    public function __call(string $name, array $arguments)
    {
        return call_user_func_array([$this->heroes, $name], $arguments);
    }

    public function addHero(Hero|Zargon $hero): self
    {
        $this->heroes->push($hero);

        return $this;
    }

    public function toArray(): array
    {
        return $this->heroes->toArray();
    }

    protected function createHeroOrZargon(array|string $data): Hero|Zargon
    {
        if (Hero::isHero($data)) {
            return Hero::from($data);
        }

        return Zargon::from($data);
    }

    protected function ensureHeroes(iterable $heroes): void
    {
        foreach ($heroes as $hero) {
            if ($hero instanceof Hero || $hero instanceof Zargon) {
                $this->addHero($hero);

                continue;
            }
            $hero = $this->createHeroOrZargon($hero);
            $this->addHero($hero);
        }
    }
}
