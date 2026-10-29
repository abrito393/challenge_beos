<?php

declare(strict_types=1);

namespace Src\Auth\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Src\Auth\Infrastructure\Persistence\EloquentUser;

/**
 * @property EloquentUser $resource
 */
final class AuthenticatedUserResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'email' => $this->resource->email,
            'role' => $this->resource->role->value,
            /** @var list<string> */
            'abilities' => $this->resource->currentAccessToken()->abilities ?? [],
        ];
    }
}
