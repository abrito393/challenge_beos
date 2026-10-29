<?php

declare(strict_types=1);

use Src\Catalog\Infrastructure\Persistence\EloquentProduct;
use Src\Currency\Infrastructure\Persistence\EloquentCurrency;

beforeEach(function (): void {
    $this->usd = EloquentCurrency::factory()->usd()->create();
});

function validProduct(array $overrides = []): array
{
    return [
        'name' => 'Laptop Pro 14',
        'description' => 'Laptop de 14 pulgadas',
        'price' => '1299.99',
        'currency_id' => test()->usd->id,
        'tax_cost' => '207.99',
        'manufacturing_cost' => '650',
        ...$overrides,
    ];
}

it('AC-002-01: creates a product and returns it with exact string amounts, embedded currency and Location', function (): void {
    actingAsAdmin();

    $response = $this->postJson('/api/v1/products', validProduct());

    $id = EloquentProduct::query()->sole()->id;
    $response->assertCreated()
        ->assertHeader('Location', route('products.show', ['product' => $id]))
        ->assertJsonPath('data.id', $id)
        ->assertJsonPath('data.price', '1299.99')
        ->assertJsonPath('data.tax_cost', '207.99')
        ->assertJsonPath('data.manufacturing_cost', '650.00')
        ->assertJsonPath('data.currency', ['id' => $this->usd->id, 'code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$'])
        ->assertJsonStructure(['data' => ['created_at', 'updated_at']]);
});

it('accepts JSON numbers for amounts without losing precision', function (): void {
    actingAsAdmin();

    $this->postJson('/api/v1/products', validProduct(['price' => 0.1, 'tax_cost' => 1299.99]))
        ->assertCreated()
        ->assertJsonPath('data.price', '0.10')
        ->assertJsonPath('data.tax_cost', '1299.99');
});

it('AC-002-02: rejects invalid payloads with Problem Details field errors', function (array $payload, array $fields): void {
    actingAsAdmin();

    $this->postJson('/api/v1/products', $payload)
        ->assertUnprocessable()
        ->assertHeader('Content-Type', 'application/problem+json')
        ->assertJsonPath('type', '/problems/validation-error')
        ->assertJsonValidationErrors($fields);

    expect(EloquentProduct::query()->count())->toBe(0);
})->with([
    'missing fields' => [[], ['name', 'description', 'price', 'currency_id', 'tax_cost', 'manufacturing_cost']],
    'negative amounts' => [fn () => validProduct(['price' => '-1', 'tax_cost' => '-0.01']), ['price', 'tax_cost']],
    'more than 2 decimals' => [fn () => validProduct(['manufacturing_cost' => '1.999']), ['manufacturing_cost']],
    'unknown currency' => [fn () => validProduct(['currency_id' => 999]), ['currency_id']],
    'too long name' => [fn () => validProduct(['name' => str_repeat('a', 256)]), ['name']],
    'amount above DECIMAL(15,2)' => [fn () => validProduct(['price' => '10000000000000']), ['price']],
]);

it('AC-002-03: ignores fields that are not allowed (no mass assignment)', function (): void {
    actingAsAdmin();

    $this->postJson('/api/v1/products', validProduct(['id' => 999, 'deleted_at' => '2020-01-01', 'created_at' => '2000-01-01']))
        ->assertCreated();

    $product = EloquentProduct::query()->sole();
    expect($product->id)->not->toBe(999)
        ->and($product->deleted_at)->toBeNull()
        ->and($product->created_at->year)->not->toBe(2000);
});

it('AC-002-16: forbids creating products with a read-only token', function (): void {
    actingAsViewer();

    $this->postJson('/api/v1/products', validProduct())->assertForbidden();

    expect(EloquentProduct::query()->count())->toBe(0);
});

it('requires authentication', function (): void {
    $this->postJson('/api/v1/products', validProduct())->assertUnauthorized();
});
