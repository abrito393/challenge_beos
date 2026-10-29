<?php

declare(strict_types=1);

namespace Src\Auth\Infrastructure\Security;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Config\Repository as Config;
use Src\Auth\Application\IssueToken\IssuedToken;
use Src\Auth\Application\IssueToken\TokenIssuer;
use Src\Auth\Domain\User;
use Src\Auth\Domain\ValueObjects\DeviceName;
use Src\Auth\Infrastructure\Persistence\EloquentUser;
use Src\Shared\Domain\Security\TokenAbility;

final readonly class SanctumTokenIssuer implements TokenIssuer
{
    public function __construct(private Config $config) {}

    public function issue(User $user, DeviceName $deviceName): IssuedToken
    {
        $abilities = $user->abilities();
        $expiresAt = $this->expiresAt();

        $token = EloquentUser::query()->findOrFail($user->id()->value())->createToken(
            $deviceName->value(),
            array_map(fn (TokenAbility $ability): string => $ability->value, $abilities),
            $expiresAt,
        );

        return new IssuedToken($token->plainTextToken, $abilities, $expiresAt);
    }

    private function expiresAt(): ?CarbonImmutable
    {
        $minutes = $this->config->get('sanctum.expiration');

        return is_numeric($minutes) ? CarbonImmutable::now()->addMinutes((int) $minutes) : null;
    }
}
