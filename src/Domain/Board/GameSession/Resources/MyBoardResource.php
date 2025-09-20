<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MyBoardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'group' => $this->group->value,
            'order' => $this->order,
        ];
    }
}
