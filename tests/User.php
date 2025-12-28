<?php

declare(strict_types=1);

namespace OffloadProject\Testerra\Tests;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use OffloadProject\Testerra\Traits\HasTestAssignments;

final class User extends Authenticatable
{
    use HasFactory, HasTestAssignments, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
