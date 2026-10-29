<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Src\Catalog\Infrastructure\Persistence\EloquentProduct;
use Src\Currency\Infrastructure\Persistence\EloquentCurrency;

beforeEach(function (): void {
    $this->usd = EloquentCurrency::factory()->usd()->create();
    $this->eur = EloquentCurrency::factory()->eur()->create();
    $this->mxn = EloquentCurrency::factory()->mxn()->create();
    $this->product = EloquentProduct::factory()->create(['currency_id' => $this->usd->id, 'price' => '100.00']);
    actingAsViewer();
});

it('AC-003-01: lists the prices of a product with embedded currency, ordered by code', function (): void {
    $this->product->prices()->create(['currency_id' => $this->mxn->id, 'price' => '1850.00']);
    $this->product->prices()->create(['currency_id' => $this->eur->id, 'price' => '89.90']);

    $this->getJson("/api/v1/products/{$this->product->id}/prices")
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.currency.code', 'EUR')
        ->assertJsonPath('data.0.price', '89.90')
        ->assertJsonPath('data.0.product_id', $this->product->id)
        ->assertJsonPath('data.1.currency.code', 'MXN')
        ->assertJsonPath('data.1.price', '1850.00');
});

it('AC-003-02: returns an empty list when the product has no extra prices', function (): void {
    $this->getJson("/api/v1/products/{$this->product->id}/prices")
        ->assertOk()
        ->assertExactJson(['data' => []]);
});

it('AC-003-03: answers 404 for missing or soft-deleted products', function (bool $deleted): void {
    $id = 999;

    if ($deleted) {
        $this->product->delete();
        $id = $this->product->id;
    }

    $this->getJson("/api/v1/products/{$id}/prices")
        ->assertNotFound()
        ->assertJsonPath('type', '/problems/product-not-found');
})->with(['missing' => false, 'soft-deleted' => true]);

it('lists prices with a constant number of queries', function (): void {
    $this->product->prices()->create(['currency_id' => $this->mxn->id, 'price' => '1850.00']);
    $this->product->prices()->create(['currency_id' => $this->eur->id, 'price' => '89.90']);

    DB::enableQueryLog();
    $this->getJson("/api/v1/products/{$this->product->id}/prices")->assertOk();
    $queries = array_filter(DB::getQueryLog(), fn (array $log): bool => str_contains($log['query'], 'product'));

    expect($queries)->toHaveCount(2); // product exists + prices joined with currencies
});
