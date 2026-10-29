<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Src\Auth\Domain\UserRole;
use Src\Auth\Infrastructure\Persistence\EloquentUser;

/**
 * @extends Factory<EloquentUser>
 */
final class UserFactory extends Factory
{
    protected $model = EloquentUser::class;

    private static ?string $password = null;

    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => self::$password ??= Hash::make('password'),
            'role' => UserRole::Viewer,
        ];
    }

    public function admin(): static
    {
        return $this->state(['role' => UserRole::Admin]);
    }

    public function viewer(): static
    {
        return $this->state(['role' => UserRole::Viewer]);
    }
}
