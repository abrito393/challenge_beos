<?php

declare(strict_types=1);

namespace Src\Auth\Infrastructure\Persistence;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Src\Auth\Domain\UserRole;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property UserRole $role
 */
final class EloquentUser extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory;

    protected $table = 'users';

    /** @var list<string> */
    protected $fillable = ['name', 'email', 'password', 'role'];

    /** @var list<string> */
    protected $hidden = ['password'];

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }
}
