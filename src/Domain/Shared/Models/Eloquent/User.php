<?php

declare(strict_types=1);

namespace Domain\Shared\Models\Eloquent;

use Database\Factories\Shared\UserFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

#[UseFactory(UserFactory::class)]
class User extends \Illuminate\Foundation\Auth\User implements \Domain\Shared\Contracts\Models\User
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'preferences',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'preferences' => 'array',
        ];
    }
}
