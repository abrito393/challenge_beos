<?php

declare(strict_types=1);

namespace Src\Auth\Application\IssueToken;

use DateTimeImmutable;
use Src\Shared\Domain\Security\TokenAbility;

final readonly class IssuedToken
{
    /**
     * @param  list<TokenAbility>  $abilities
     */
    public function __construct(
        #[\SensitiveParameter]
        public string $plainTextToken,
        public array $abilities,
        public ?DateTimeImmutable $expiresAt,
    ) {}
}
