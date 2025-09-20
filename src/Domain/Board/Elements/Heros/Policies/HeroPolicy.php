<?php

declare(strict_types=1);

namespace Domain\Board\Elements\Heros\Policies;

use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Auth\Authenticatable;
use Smorken\Domain\Authorization\Policies\ModelBasedPolicy;
use Smorken\Model\Contracts\Model;

class HeroPolicy extends ModelBasedPolicy
{
    protected function isUserAllowedToUpdate(Authenticatable $user, Model $model): Response
    {
        if ($model->user_id !== $user->id) {
            return $this->deny('You are not allowed to update this hero.');
        }

        return $this->allow();
    }

    protected function isUserAllowedToView(Authenticatable $user, Model $model): Response
    {
        if ($model->user_id !== $user->id) {
            return $this->deny('You are not allowed to view this hero.');
        }

        return $this->allow();
    }

    protected function isUserAllowedToViewAny(Authenticatable $user): Response
    {
        return $this->allow();
    }
}
