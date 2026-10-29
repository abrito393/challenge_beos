<?php

declare(strict_types=1);

use Src\Catalog\Infrastructure\Persistence\EloquentProduct;
use Src\Currency\Infrastructure\Persistence\EloquentCurrency;

beforeEach(function (): void {
    $this->eur = EloquentCurrency::factory()->eur()->create();
    $this->product = EloquentProduct::factory()->create();
});

function replacement(array $overrides = []): array
{
    return [
        'name' => 'Laptop Pro 16',
        'description' => 'Laptop de 16 pulgadas',
        'price' => '1999.50',
        'currency_id' => test()->eur->id,
        'tax_cost' => '300',
        'manufacturing_cost' => '900.10',
        ...$overrides,
    ];
}

it('AC-002-12: replaces every field of the product', function (): void {
    actingAsAdmin();

    $this->putJson("/api/v1/products/{$this->product->id}", replacement())
        ->assertOk()
        ->assertJsonPath('data.name', 'Laptop Pro 16')
        ->assertJsonPath('data.price', '1999.50')
        ->assertJsonPath('data.currency.code', 'EUR')
        ->assertJsonPath('data.tax_cost', '300.00');

    expect($this->product->fresh())
        ->name->toBe('Laptop Pro 16')
        ->manufacturing_cost->toBe('900.10')
        ->currency_id->toBe($this->eur->id);
});

it('AC-002-13: PUT is a full replacement, so missing fields are rejected', function (): void {
    actingAsAdmin();
    $payload = replacement();
    unset($payload['description']);

    $this->putJson("/api/v1/products/{$this->product->id}", $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('description');
});

it('AC-002-11: answers 404 when updating a missing product', function (): void {
    actingAsAdmin();

    $this->putJson('/api/v1/products/999', replacement())
        ->assertNotFound()
        ->assertJsonPath('type', '/problems/product-not-found');
});

it('AC-002-16: forbids updates with a read-only token', function (): void {
    actingAsViewer();

    $this->putJson("/api/v1/products/{$this->product->id}", replacement())->assertForbidden();
});
