<?php

declare(strict_types=1);

use Src\Catalog\Infrastructure\Persistence\EloquentProduct;

it('AC-002-14: soft deletes the product', function (): void {
    actingAsAdmin();
    $product = EloquentProduct::factory()->create();

    $this->deleteJson("/api/v1/products/{$product->id}")->assertNoContent();

    $this->assertSoftDeleted('products', ['id' => $product->id]);
});

it('AC-002-15: answers 404 when deleting an already deleted product', function (): void {
    actingAsAdmin();
    $product = EloquentProduct::factory()->create();

    $this->deleteJson("/api/v1/products/{$product->id}")->assertNoContent();
    $this->deleteJson("/api/v1/products/{$product->id}")
        ->assertNotFound()
        ->assertJsonPath('type', '/problems/product-not-found');
});

it('AC-002-16: forbids deletes with a read-only token', function (): void {
    actingAsViewer();
    $product = EloquentProduct::factory()->create();

    $this->deleteJson("/api/v1/products/{$product->id}")->assertForbidden();

    $this->assertNotSoftDeleted('products', ['id' => $product->id]);
});
