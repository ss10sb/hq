<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\Constants;

enum Status: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case ABORTED = 'aborted';
}
