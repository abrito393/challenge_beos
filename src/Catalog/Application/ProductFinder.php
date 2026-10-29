<?php

declare(strict_types=1);

namespace Src\Catalog\Application;

use Src\Catalog\Domain\ValueObjects\ProductId;

/** Read port: a single product ready for presentation. Ignores soft-deleted products. */
interface ProductFinder
{
    public function find(ProductId $id): ?ProductView;
}
