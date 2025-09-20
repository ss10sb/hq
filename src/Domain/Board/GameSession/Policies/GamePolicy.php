<?php

declare(strict_types=1);

namespace Domain\Board\GameSession\Policies;

use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Auth\Authenticatable;
use Smorken\Domain\Authorization\Policies\ModelBasedPolicy;
use Smorken\Model\Contracts\Model;

class GamePolicy extends ModelBasedPolicy
{
    protected function isUserAllowedToCreate(Authenticatable $user, ?array $attributes = null): Response
    {
        return Response::allow();
    }

    protected function isUserAllowedToDestroy(Authenticatable $user, Model $model): Response
    {
        if ($user->id === $model->game_master_id) {
            return Response::allow();
        }

        return Response::deny('You are not allowed to delete this game.');
    }

    protected function isUserAllowedToUpdate(Authenticatable $user, Model $model): Response
    {
        if ($user->id === $model->game_master_id) {
            return Response::allow();
        }

        return Response::deny('You are not allowed to update this game.');
    }

    protected function isUserAllowedToView(Authenticatable $user, Model $model): Response
    {
        return Response::allow();
    }

    protected function isUserAllowedToViewAny(Authenticatable $user): Response
    {
        return Response::allow();
    }
}
