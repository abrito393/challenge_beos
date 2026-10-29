<?php

declare(strict_types=1);

namespace Src\Catalog\Infrastructure\Http\Resources;

use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Src\Catalog\Application\ProductView;

/**
 * @property ProductView $resource
 */
final class ProductResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $product = $this->resource;

        return [
            'id' => $product->id,
            /** @example Laptop Pro 14 */
            'name' => $product->name,
            'description' => $product->description,
            /**
             * Price in the base currency, as a decimal string (never a float).
             *
             * @example 1299.99
             */
            'price' => $product->price,
            'currency' => [
                'id' => $product->currency->id,
                /** @example USD */
                'code' => $product->currency->code,
                'name' => $product->currency->name,
                'symbol' => $product->currency->symbol,
            ],
            /** @example 207.99 */
            'tax_cost' => $product->taxCost,
            /** @example 650.00 */
            'manufacturing_cost' => $product->manufacturingCost,
            'created_at' => self::utc($product->createdAt),
            'updated_at' => self::utc($product->updatedAt),
        ];
    }

    private static function utc(DateTimeImmutable $date): string
    {
        return $date->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s\Z');
    }
}
