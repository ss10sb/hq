<?php

declare(strict_types=1);

namespace Domain\Shared\Contracts\Models;

use Illuminate\Contracts\Auth\Authenticatable;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property array $preferences
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
interface User extends Authenticatable {}
