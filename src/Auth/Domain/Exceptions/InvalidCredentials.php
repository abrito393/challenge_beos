<?php

declare(strict_types=1);

namespace Src\Auth\Domain\Exceptions;

use Src\Shared\Domain\Exceptions\AuthenticationFailed;

/**
 * Deliberately generic: never reveals whether the email exists (AC-004-02).
 */
final class InvalidCredentials extends AuthenticationFailed
{
    public static function create(): self
    {
        return new self('The provided credentials are incorrect.');
    }

    public function errorCode(): string
    {
        return 'invalid-credentials';
    }
}
