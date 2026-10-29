<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Src\Auth\Domain\UserRole;
use Src\Auth\Infrastructure\Persistence\EloquentUser;

/**
 * Demo credentials for reviewers. Never runs in production (see DatabaseSeeder).
 */
final class UserSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([['Admin', 'admin@example.com', UserRole::Admin], ['Viewer', 'viewer@example.com', UserRole::Viewer]] as [$name, $email, $role]) {
            EloquentUser::query()->updateOrCreate(
                ['email' => $email],
                ['name' => $name, 'password' => 'password', 'role' => $role],
            );
        }
    }
}
