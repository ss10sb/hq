<?php

declare(strict_types=1);

namespace Domain\Board\Elements\DataObjects;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Smorken\Domain\DataObjects\DataObject;

/**
 * @method bool isEmpty()
 * @method bool isNotEmpty()
 */
final class Elements extends DataObject
{
    public Collection $elements;

    public function __construct(iterable $elements = [])
    {
        $this->elements = new Collection;
        $this->ensureElements($elements);
    }

    public function addElement(Element $element): self
    {
        $this->elements->push($element);

        return $this;
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
        return self::fromArray($request->input('elements') ?? []);
    }

    public function __call(string $name, array $arguments)
    {
        return call_user_func_array([$this->elements, $name], $arguments);
    }

    public function toArray(): array
    {
        // Serialize each element explicitly to ensure nested properties (like traversable) are persisted
        return $this->elements->map(function (Element $el): array {
            $data = [
                'id' => $el->id,
                'name' => $el->name,
                'type' => $el->type->value,
                'x' => $el->x,
                'y' => $el->y,
                // Flatten properties to match how the frontend sends/consumes elements
                'interactive' => $el->properties->interactive,
                'hidden' => $el->properties->hidden,
                'traversable' => $el->properties->traversable,
                // Stats are optional and only present for monsters; keep original array shape if available
                'stats' => $el->stats?->toArray(),
            ];

            // Include trap-specific fields when present so they persist across saves
            if ($el->trapType !== null) {
                $data['trapType'] = $el->trapType->value;
            }
            if ($el->trapStatus !== null) {
                $data['trapStatus'] = $el->trapStatus->value;
            }

            return $data;
        })->toArray();
    }

    protected function ensureElements(iterable $elements): void
    {
        foreach ($elements as $element) {
            if ($element instanceof Element) {
                $this->addElement($element);

                continue;
            }
            $element = Element::from($element);
            $this->addElement($element);
        }
    }
}
