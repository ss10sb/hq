<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MyGameResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'joinKey' => $this->join_key,
            'gameMasterId' => $this->game_master_id,
            'status' => $this->status->value,
            'board' => MyBoardResource::make($this->board),
        ];
    }
}
