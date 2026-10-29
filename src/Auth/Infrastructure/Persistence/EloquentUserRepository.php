<?php

declare(strict_types=1);

namespace Src\Auth\Infrastructure\Persistence;

use Src\Auth\Domain\User;
use Src\Auth\Domain\UserRepository;
use Src\Auth\Domain\ValueObjects\Email;
use Src\Auth\Domain\ValueObjects\UserId;

final class EloquentUserRepository implements UserRepository
{
    public function findByEmail(Email $email): ?User
    {
        $model = EloquentUser::query()->where('email', $email->value())->first();

        return $model === null ? null : self::toDomain($model);
    }

    private static function toDomain(EloquentUser $model): User
    {
        return new User(
            new UserId($model->id),
            new Email($model->email),
            $model->role,
            $model->password,
        );
    }
}
