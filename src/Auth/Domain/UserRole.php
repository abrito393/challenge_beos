<?php

declare(strict_types=1);

namespace Src\Auth\Domain;

use Src\Shared\Domain\Security\TokenAbility;

enum UserRole: string
{
    case Admin = 'admin';
    case Viewer = 'viewer';

    /**
     * Abilities are derived server-side from the role; clients cannot request them (AC-004-06).
     *
     * @return list<TokenAbility>
     */
    public function abilities(): array
    {
        return match ($this) {
            self::Admin => [TokenAbility::ProductsRead, TokenAbility::ProductsWrite],
            self::Viewer => [TokenAbility::ProductsRead],
        };
    }
}
