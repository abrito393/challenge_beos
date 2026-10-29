<?php

declare(strict_types=1);

namespace Src\Catalog\Infrastructure\Persistence;

use Src\Catalog\Application\ProductFinder;
use Src\Catalog\Application\ProductView;
use Src\Catalog\Domain\ValueObjects\ProductId;

final class EloquentProductFinder implements ProductFinder
{
    public function find(ProductId $id): ?ProductView
    {
        $row = ProductViewQuery::base()->where('products.id', $id->value())->first();

        return $row === null ? null : ProductViewQuery::toView($row);
    }
}
