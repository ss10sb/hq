<?php

declare(strict_types=1);

namespace Domain\Board\Elements\Constants;

enum ElementType: string
{
    case DOOR = 'door';
    case SECRET_DOOR = 'secret_door';
    case MONSTER = 'monster';
    case PLAYER_START = 'player_start';
    case PLAYER_EXIT = 'player_exit';
    case TRAP = 'trap';
    case TREASURE = 'treasure';
    case CUSTOM = 'custom';
}
