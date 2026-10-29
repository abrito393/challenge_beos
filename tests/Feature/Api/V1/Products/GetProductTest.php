<?php

declare(strict_types=1);

use Src\Catalog\Infrastructure\Persistence\EloquentProduct;
use Src\Currency\Infrastructure\Persistence\EloquentCurrency;

it('AC-002-10: returns a product with its currency', function (): void {
    actingAsViewer();
    $product = EloquentProduct::factory()->create([
        'name' => 'Laptop', 'price' => '1299.99', 'currency_id' => EloquentCurrency::factory()->eur(),
    ]);

    $this->getJson("/api/v1/products/{$product->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $product->id)
        ->assertJsonPath('data.name', 'Laptop')
        ->assertJsonPath('data.price', '1299.99')
        ->assertJsonPath('data.currency.code', 'EUR');
});

it('AC-002-11: answers 404 Problem Details for missing or deleted products', function (bool $deleted): void {
    actingAsViewer();
    $id = 999;

    if ($deleted) {
        $product = EloquentProduct::factory()->create();
        $product->delete();
        $id = $product->id;
    }

    $this->getJson("/api/v1/products/{$id}")
        ->assertNotFound()
        ->assertHeader('Content-Type', 'application/problem+json')
        ->assertJsonPath('type', '/problems/product-not-found')
        ->assertJsonPath('detail', "Product {$id} was not found.");
})->with(['missing' => false, 'soft-deleted' => true]);

it('answers 404 for non-numeric ids without reaching the database', function (): void {
    actingAsViewer();

    $this->getJson('/api/v1/products/abc')->assertNotFound()->assertJsonPath('type', '/problems/not-found');
});
