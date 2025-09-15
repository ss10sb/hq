<?php

declare(strict_types=1);

namespace Domain\Board\GameBoard\Policies;

use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Auth\Authenticatable;
use Smorken\Domain\Authorization\Policies\ModelBasedPolicy;
use Smorken\Model\Contracts\Model;

class BoardPolicy extends ModelBasedPolicy
{
    protected function isUserAllowedToDestroy(Authenticatable $user, Model $model): Response
    {
        if ($model->creator_id === $user->id) {
            return Response::allow();
        }

        return Response::deny('You are not allowed to delete this board.');
    }

    protected function isUserAllowedToUpdate(Authenticatable $user, Model $model): Response
    {
        if ($model->creator_id === $user->id) {
            return Response::allow();
        }

        return Response::deny('You are not allowed to update this board.');
    }

    protected function isUserAllowedToView(Authenticatable $user, Model $model): Response
    {
        if ($model->is_public) {
            return Response::allow();
        }
        if ($model->creator_id === $user->id) {
            return Response::allow();
        }

        return Response::deny('You are not allowed to view this board.');
    }

    protected function isUserAllowedToViewAny(Authenticatable $user): Response
    {
        return Response::allow();
    }
}
