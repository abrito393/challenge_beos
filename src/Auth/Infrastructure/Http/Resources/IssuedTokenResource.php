<?php

declare(strict_types=1);

namespace Src\Auth\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Src\Auth\Application\IssueToken\IssuedToken;
use Src\Shared\Domain\Security\TokenAbility;

/**
 * @property IssuedToken $resource
 */
final class IssuedTokenResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            /** @example 1|zQmXKx3yW8... */
            'token' => $this->resource->plainTextToken,
            'token_type' => 'Bearer',
            /** @var list<string> */
            'abilities' => array_map(fn (TokenAbility $ability): string => $ability->value, $this->resource->abilities),
            'expires_at' => $this->resource->expiresAt?->format(DATE_ATOM),
        ];
    }
}
