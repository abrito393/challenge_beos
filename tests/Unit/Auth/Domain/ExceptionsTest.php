<?php

declare(strict_types=1);

use Src\Auth\Domain\Exceptions\InvalidCredentials;
use Src\Auth\Domain\Exceptions\InvalidDeviceName;
use Src\Auth\Domain\Exceptions\InvalidEmail;
use Src\Auth\Domain\Exceptions\InvalidUserId;
use Src\Shared\Domain\Exceptions\AuthenticationFailed;
use Src\Shared\Domain\Exceptions\DomainException;
use Src\Shared\Domain\Exceptions\InvalidValue;

it('exposes stable error codes and categories for every auth error', function (DomainException $exception, string $code, string $category): void {
    expect($exception->errorCode())->toBe($code)
        ->and($exception)->toBeInstanceOf($category);
})->with([
    'invalid credentials' => fn () => [InvalidCredentials::create(), 'invalid-credentials', AuthenticationFailed::class],
    'invalid email' => fn () => [InvalidEmail::because('x'), 'invalid-email', InvalidValue::class],
    'invalid device name' => fn () => [InvalidDeviceName::because(''), 'invalid-device-name', InvalidValue::class],
    'invalid user id' => fn () => [InvalidUserId::because(0), 'invalid-user-id', InvalidValue::class],
]);
