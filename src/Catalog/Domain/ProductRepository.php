<?php

declare(strict_types=1);

namespace Src\Catalog\Domain;

use Src\Catalog\Domain\ValueObjects\ProductId;

/** Write port: aggregates in, aggregates out. Reads for display go through ProductFinder. */
interface ProductRepository
{
    /** Ignores soft-deleted products. */
    public function find(ProductId $id): ?Product;

    /** Inserts or updates; returns the identity of the stored product. */
    public function save(Product $product): ProductId;

    public function delete(ProductId $id): void;
}
