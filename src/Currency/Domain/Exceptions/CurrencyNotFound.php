<?php

declare(strict_types=1);

namespace Src\Currency\Domain\Exceptions;

use Src\Shared\Domain\Exceptions\NotFound;

final class CurrencyNotFound extends NotFound
{
    public static function withId(int $id): self
    {
        return new self(sprintf('Currency %d was not found.', $id));
    }

    public function errorCode(): string
    {
        return 'currency-not-found';
    }
}
