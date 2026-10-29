<?php

declare(strict_types=1);

namespace Src\Auth\Application\IssueToken;

use Src\Auth\Domain\Exceptions\InvalidCredentials;
use Src\Auth\Domain\Exceptions\InvalidEmail;
use Src\Auth\Domain\PasswordHasher;
use Src\Auth\Domain\UserRepository;
use Src\Auth\Domain\ValueObjects\DeviceName;
use Src\Auth\Domain\ValueObjects\Email;

final readonly class IssueTokenUseCase
{
    public function __construct(
        private UserRepository $users,
        private PasswordHasher $hasher,
        private TokenIssuer $tokens,
    ) {}

    /**
     * @throws InvalidCredentials when the email is unknown or the password does not match
     */
    public function execute(IssueTokenCommand $command): IssuedToken
    {
        $deviceName = new DeviceName($command->deviceName);

        try {
            $user = $this->users->findByEmail(new Email($command->email));
        } catch (InvalidEmail) {
            $user = null;
        }

        // Always hash, even for unknown users, so timing does not reveal which emails exist.
        $passwordMatches = $this->hasher->verify($command->password, $user?->passwordHash());

        if ($user === null || ! $passwordMatches) {
            throw InvalidCredentials::create();
        }

        return $this->tokens->issue($user, $deviceName);
    }
}
