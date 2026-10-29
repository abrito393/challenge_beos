<?php

declare(strict_types=1);

namespace Src\Shared\Domain\Exceptions;

/** Category: The operation conflicts with the current state of the resource. */
abstract class Conflict extends DomainException {}
