<?php

declare(strict_types=1);

namespace Src\Catalog\Infrastructure\Http\Resources;

use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Src\Catalog\Application\ProductPriceView;

/**
 * @property ProductPriceView $resource
 */
final class ProductPriceResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $price = $this->resource;

        return [
            'id' => $price->id,
            'product_id' => $price->productId,
            /**
             * Price in this currency, as a decimal string.
             *
             * @example 92.00
             */
            'price' => $price->price,
            'currency' => [
                'id' => $price->currency->id,
                /** @example EUR */
                'code' => $price->currency->code,
                'name' => $price->currency->name,
                'symbol' => $price->currency->symbol,
            ],
            'created_at' => self::utc($price->createdAt),
            'updated_at' => self::utc($price->updatedAt),
        ];
    }

    private static function utc(DateTimeImmutable $date): string
    {
        return $date->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s\Z');
    }
}
