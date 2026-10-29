<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Src\Catalog\Infrastructure\Persistence\EloquentProduct;
use Src\Currency\Infrastructure\Persistence\EloquentCurrency;

beforeEach(function (): void {
    $this->usd = EloquentCurrency::factory()->usd()->create();
    $this->eur = EloquentCurrency::factory()->eur()->create();
    actingAsViewer();
});

function names(TestResponse $response): array
{
    return array_column($response->json('data'), 'name');
}

it('AC-002-04: paginates with data, links and meta; 15 per page by default', function (): void {
    EloquentProduct::factory()->count(20)->create(['currency_id' => $this->usd->id]);

    $this->getJson('/api/v1/products')
        ->assertOk()
        ->assertJsonCount(15, 'data')
        ->assertJsonStructure(['data', 'links' => ['first', 'last', 'prev', 'next'], 'meta' => ['current_page', 'per_page', 'total', 'last_page']])
        ->assertJsonPath('meta.total', 20)
        ->assertJsonPath('meta.per_page', 15);

    $this->getJson('/api/v1/products?per_page=5&page=4')
        ->assertJsonCount(5, 'data')
        ->assertJsonPath('meta.current_page', 4);
});

it('AC-002-04: caps per_page at 100', function (): void {
    $this->getJson('/api/v1/products?per_page=101')->assertUnprocessable()->assertJsonValidationErrors('per_page');
});

it('AC-002-05: filters by name, case-insensitive', function (): void {
    EloquentProduct::factory()->create(['name' => 'Laptop Pro', 'currency_id' => $this->usd->id]);
    EloquentProduct::factory()->create(['name' => 'Gaming LAPTOP', 'currency_id' => $this->usd->id]);
    EloquentProduct::factory()->create(['name' => 'Phone', 'currency_id' => $this->usd->id]);

    $response = $this->getJson('/api/v1/products?filter[name]=lap&sort=name')->assertOk();

    expect(names($response))->toBe(['Gaming LAPTOP', 'Laptop Pro']);
});

it('AC-002-05: treats LIKE wildcards in the name filter literally', function (): void {
    EloquentProduct::factory()->create(['name' => '100% Cotton', 'currency_id' => $this->usd->id]);
    EloquentProduct::factory()->create(['name' => '100 Cotton', 'currency_id' => $this->usd->id]);

    expect(names($this->getJson('/api/v1/products?filter[name]=100%25')))->toBe(['100% Cotton']);
});

it('AC-002-06: combines currency and price range filters with AND', function (): void {
    EloquentProduct::factory()->create(['name' => 'match', 'price' => '25.00', 'currency_id' => $this->eur->id]);
    EloquentProduct::factory()->create(['name' => 'other currency', 'price' => '25.00', 'currency_id' => $this->usd->id]);
    EloquentProduct::factory()->create(['name' => 'too cheap', 'price' => '9.99', 'currency_id' => $this->eur->id]);
    EloquentProduct::factory()->create(['name' => 'too expensive', 'price' => '50.01', 'currency_id' => $this->eur->id]);
    EloquentProduct::factory()->create(['name' => 'edge', 'price' => '50.00', 'currency_id' => $this->eur->id]);

    $response = $this->getJson("/api/v1/products?filter[currency_id]={$this->eur->id}&filter[min_price]=10&filter[max_price]=50&sort=price");

    expect(names($response))->toBe(['match', 'edge']);
});

it('AC-002-06: rejects inconsistent or unknown filters', function (string $query, string $field): void {
    $this->getJson("/api/v1/products?{$query}")->assertUnprocessable()->assertJsonValidationErrors($field);
})->with([
    'max below min' => ['filter[min_price]=50&filter[max_price]=10', 'filter.max_price'],
    'unknown filter key' => ['filter[stock]=1', 'filter'],
]);

it('AC-002-07: sorts by whitelisted fields in both directions', function (): void {
    EloquentProduct::factory()->create(['name' => 'B', 'price' => '20.00', 'currency_id' => $this->usd->id]);
    EloquentProduct::factory()->create(['name' => 'A', 'price' => '30.00', 'currency_id' => $this->usd->id]);
    EloquentProduct::factory()->create(['name' => 'C', 'price' => '10.00', 'currency_id' => $this->usd->id]);

    expect(names($this->getJson('/api/v1/products?sort=-price')))->toBe(['A', 'B', 'C'])
        ->and(names($this->getJson('/api/v1/products?sort=price')))->toBe(['C', 'B', 'A'])
        ->and(names($this->getJson('/api/v1/products?sort=name')))->toBe(['A', 'B', 'C'])
        ->and(names($this->getJson('/api/v1/products?sort=-name')))->toBe(['C', 'B', 'A']);
});

it('AC-002-07: sorts by newest first by default', function (): void {
    $this->travelTo(now()->subDay());
    EloquentProduct::factory()->create(['name' => 'old', 'currency_id' => $this->usd->id]);
    $this->travelBack();
    EloquentProduct::factory()->create(['name' => 'new', 'currency_id' => $this->usd->id]);

    expect(names($this->getJson('/api/v1/products')))->toBe(['new', 'old']);
});

it('AC-002-07: rejects sorting by a non-whitelisted field', function (): void {
    $this->getJson('/api/v1/products?sort=deleted_at')
        ->assertUnprocessable()
        ->assertJsonValidationErrors('sort');
});

it('AC-002-08: hides soft-deleted products', function (): void {
    EloquentProduct::factory()->create(['name' => 'visible', 'currency_id' => $this->usd->id]);
    EloquentProduct::factory()->create(['name' => 'deleted', 'currency_id' => $this->usd->id])->delete();

    expect(names($this->getJson('/api/v1/products')))->toBe(['visible']);
});

it('AC-002-09: lists without N+1 queries, regardless of the number of products', function (int $products): void {
    EloquentProduct::factory()->count($products)->create(['currency_id' => $this->usd->id]);
    EloquentProduct::factory()->count($products)->create(['currency_id' => $this->eur->id]);

    DB::enableQueryLog();
    $this->getJson('/api/v1/products?per_page=100')->assertOk();
    $productQueries = array_filter(DB::getQueryLog(), fn (array $log): bool => str_contains($log['query'], 'products'));

    expect($productQueries)->toHaveCount(2); // count(*) + page, joined with currencies
})->with([1, 20]);
