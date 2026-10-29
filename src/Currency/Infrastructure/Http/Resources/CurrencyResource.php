<?php

declare(strict_types=1);

namespace Src\Currency\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Src\Currency\Domain\Currency;

/**
 * @property Currency $resource
 */
final class CurrencyResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id()->value(),
            /** @example EUR */
            'code' => $this->resource->code()->value(),
            /** @example Euro */
            'name' => $this->resource->name()->value(),
            /** @example € */
            'symbol' => $this->resource->symbol()->value(),
            /**
             * Units of this currency per 1 unit of the reference currency, as a decimal string.
             *
             * @example 0.92000000
             */
            'exchange_rate' => $this->resource->exchangeRate()->value(),
        ];
    }
}
