<?php

declare(strict_types=1);

use Src\Auth\Application\IssueToken\IssuedToken;
use Src\Auth\Application\IssueToken\IssueTokenCommand;
use Src\Auth\Application\IssueToken\IssueTokenUseCase;
use Src\Auth\Application\IssueToken\TokenIssuer;
use Src\Auth\Domain\Exceptions\InvalidCredentials;
use Src\Auth\Domain\PasswordHasher;
use Src\Auth\Domain\User;
use Src\Auth\Domain\UserRepository;
use Src\Auth\Domain\UserRole;
use Src\Auth\Domain\ValueObjects\DeviceName;
use Src\Auth\Domain\ValueObjects\Email;
use Src\Auth\Domain\ValueObjects\UserId;
use Src\Shared\Domain\Security\TokenAbility;

beforeEach(function (): void {
    $this->users = new class implements UserRepository
    {
        /** @var array<string, User> */
        public array $byEmail = [];

        public function findByEmail(Email $email): ?User
        {
            return $this->byEmail[$email->value()] ?? null;
        }
    };

    // Plain-text "hashes" keep the fake obvious; it records every call to assert timing safety.
    $this->hasher = new class implements PasswordHasher
    {
        /** @var list<?string> */
        public array $verifiedHashes = [];

        public function verify(string $plainPassword, ?string $hash): bool
        {
            $this->verifiedHashes[] = $hash;

            return $hash !== null && $hash === "hashed:{$plainPassword}";
        }
    };

    $this->issuer = new class implements TokenIssuer
    {
        public ?DeviceName $issuedFor = null;

        public function issue(User $user, DeviceName $deviceName): IssuedToken
        {
            $this->issuedFor = $deviceName;

            return new IssuedToken('1|plain-token', $user->abilities(), new DateTimeImmutable('2026-10-30T00:00:00Z'));
        }
    };

    $this->useCase = new IssueTokenUseCase($this->users, $this->hasher, $this->issuer);

    $this->users->byEmail['viewer@example.com'] = new User(
        new UserId(1), new Email('viewer@example.com'), UserRole::Viewer, 'hashed:secret',
    );
});

it('AC-004-06: issues a token with the abilities of the user role', function (): void {
    $token = $this->useCase->execute(new IssueTokenCommand('Viewer@Example.com', 'secret', 'cli'));

    expect($token->abilities)->toBe([TokenAbility::ProductsRead])
        ->and($this->issuer->issuedFor?->value())->toBe('cli');
});

it('AC-004-02: rejects a wrong password', function (): void {
    $this->useCase->execute(new IssueTokenCommand('viewer@example.com', 'wrong', 'cli'));
})->throws(InvalidCredentials::class, 'The provided credentials are incorrect.');

it('AC-004-02: rejects an unknown email with the same error, still spending a hash check', function (): void {
    expect(fn () => $this->useCase->execute(new IssueTokenCommand('ghost@example.com', 'secret', 'cli')))
        ->toThrow(InvalidCredentials::class, 'The provided credentials are incorrect.')
        ->and($this->hasher->verifiedHashes)->toBe([null])
        ->and($this->issuer->issuedFor)->toBeNull();
});

it('AC-004-02: treats a malformed email as invalid credentials, not as a different error', function (): void {
    $this->useCase->execute(new IssueTokenCommand('not-an-email', 'secret', 'cli'));
})->throws(InvalidCredentials::class);
