<?php

declare(strict_types=1);

namespace Src\Shared\Domain\Exceptions;

/**
 * Base for every business rule violation. The domain states *what* went wrong
 * through a category subclass; infrastructure decides how to present it (ADR-0010).
 */
abstract class DomainException extends \DomainException
{
    /** Stable, machine-readable identifier (kebab-case), e.g. "invalid-credentials". */
    abstract public function errorCode(): string;
}
