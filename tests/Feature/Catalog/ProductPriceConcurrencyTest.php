<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Src\Catalog\Domain\Exceptions\PriceAlreadyExists;
use Src\Catalog\Domain\ProductRepository;
use Src\Catalog\Domain\ValueObjects\ProductId;
use Src\Catalog\Infrastructure\Persistence\EloquentProduct;
use Src\Currency\Infrastructure\Persistence\EloquentCurrency;
use Src\Shared\Domain\ValueObjects\Money;

it('AC-003-09: a concurrent duplicate is rejected by the database and surfaces as PriceAlreadyExists', function (): void {
    $usd = EloquentCurrency::factory()->usd()->create();
    $eur = EloquentCurrency::factory()->eur()->create();
    $product = EloquentProduct::factory()->create(['currency_id' => $usd->id]);
    $repository = app(ProductRepository::class);

    // Request A loads the aggregate: no EUR price yet, so the domain check passes...
    $aggregate = $repository->find(new ProductId($product->id));
    $aggregate?->addPriceIn(Money::of('89.90', $eur->id));

    // ...meanwhile request B commits a EUR price.
    DB::table('product_prices')->insert([
        'product_id' => $product->id, 'currency_id' => $eur->id, 'price' => '95.00', 'created_at' => now(), 'updated_at' => now(),
    ]);

    expect(fn () => $repository->save($aggregate))->toThrow(PriceAlreadyExists::class)
        ->and(DB::table('product_prices')->pluck('price')->all())->toBe(['95.00']);
});
