<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\Constants;

enum PlayerJoinedRoom: string
{
    case WAITING_ROOM = 'waiting_room';
    case GAME = 'game';
}
