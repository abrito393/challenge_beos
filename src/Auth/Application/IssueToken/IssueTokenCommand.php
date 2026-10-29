<?php

declare(strict_types=1);

namespace Src\Auth\Application\IssueToken;

final readonly class IssueTokenCommand
{
    public function __construct(
        public string $email,
        #[\SensitiveParameter]
        public string $password,
        public string $deviceName,
    ) {}
}
