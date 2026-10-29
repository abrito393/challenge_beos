<?php

declare(strict_types=1);

use Src\Catalog\Infrastructure\Persistence\EloquentProduct;
use Src\Catalog\Infrastructure\Persistence\EloquentProductPrice;
use Src\Currency\Infrastructure\Persistence\EloquentCurrency;

beforeEach(function (): void {
    $this->usd = EloquentCurrency::factory()->usd()->create();
    $this->eur = EloquentCurrency::factory()->eur()->create();
    $this->mxn = EloquentCurrency::factory()->mxn()->create();
    $this->product = EloquentProduct::factory()->create(['currency_id' => $this->usd->id, 'price' => '100.00']);
    $this->uri = "/api/v1/products/{$this->product->id}/prices";
});

it('AC-003-04: stores an explicit price exactly', function (): void {
    actingAsAdmin();

    $this->postJson($this->uri, ['currency_id' => $this->eur->id, 'price' => '89.90'])
        ->assertCreated()
        ->assertJsonPath('data.price', '89.90')
        ->assertJsonPath('data.product_id', $this->product->id)
        ->assertJsonPath('data.currency.code', 'EUR');

    $this->assertDatabaseHas('product_prices', ['product_id' => $this->product->id, 'currency_id' => $this->eur->id, 'price' => '89.90']);
});

it('AC-003-05: computes the price from exchange rates when omitted', function (): void {
    actingAsAdmin();

    $this->postJson($this->uri, ['currency_id' => $this->eur->id])
        ->assertCreated()
        ->assertJsonPath('data.price', '92.00');
});

it('AC-003-05: computes cross-currency prices for non-reference base currencies', function (): void {
    actingAsAdmin();
    $product = EloquentProduct::factory()->create(['currency_id' => $this->eur->id, 'price' => '92.00']);

    $this->postJson("/api/v1/products/{$product->id}/prices", ['currency_id' => $this->mxn->id, 'price' => null])
        ->assertCreated()
        ->assertJsonPath('data.price', '1850.00');
});

it('AC-003-06: answers 409 when the currency already has a price', function (): void {
    actingAsAdmin();
    $this->postJson($this->uri, ['currency_id' => $this->eur->id, 'price' => '89.90'])->assertCreated();

    $this->postJson($this->uri, ['currency_id' => $this->eur->id, 'price' => '95.00'])
        ->assertConflict()
        ->assertHeader('Content-Type', 'application/problem+json')
        ->assertJsonPath('type', '/problems/price-already-exists');

    expect(EloquentProductPrice::query()->count())->toBe(1);
});

it('AC-003-07: answers 422 for the product base currency', function (): void {
    actingAsAdmin();

    $this->postJson($this->uri, ['currency_id' => $this->usd->id, 'price' => '100.00'])
        ->assertUnprocessable()
        ->assertJsonPath('type', '/problems/price-in-base-currency');

    expect(EloquentProductPrice::query()->count())->toBe(0);
});

it('AC-003-08: validates currency and amount', function (array $payload, string $field): void {
    actingAsAdmin();

    $this->postJson($this->uri, $payload)
        ->assertUnprocessable()
        ->assertJsonPath('type', '/problems/validation-error')
        ->assertJsonValidationErrors($field);
})->with([
    'missing currency' => [['price' => '1.00'], 'currency_id'],
    'unknown currency' => [['currency_id' => 999], 'currency_id'],
    'negative price' => [fn () => ['currency_id' => test()->eur->id, 'price' => '-1'], 'price'],
    'too many decimals' => [fn () => ['currency_id' => test()->eur->id, 'price' => '1.001'], 'price'],
]);

it('AC-003-03: answers 404 when posting to a missing or deleted product', function (): void {
    actingAsAdmin();
    $this->product->delete();

    $this->postJson($this->uri, ['currency_id' => $this->eur->id, 'price' => '1.00'])
        ->assertNotFound()
        ->assertJsonPath('type', '/problems/product-not-found');
});

it('AC-003-10: forbids creating prices with a read-only token', function (): void {
    actingAsViewer();

    $this->postJson($this->uri, ['currency_id' => $this->eur->id, 'price' => '1.00'])->assertForbidden();
});
