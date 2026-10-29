<?php

declare(strict_types=1);

use Src\Currency\Infrastructure\Persistence\EloquentCurrency;

it('AC-001-01: lists currencies ordered by code with exchange rates as strings', function (): void {
    EloquentCurrency::factory()->usd()->create();
    EloquentCurrency::factory()->eur()->create();
    actingAsViewer();

    $this->getJson('/api/v1/currencies')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0', [
            'id' => EloquentCurrency::query()->where('code', 'EUR')->value('id'),
            'code' => 'EUR',
            'name' => 'Euro',
            'symbol' => '€',
            'exchange_rate' => '0.92000000',
        ])
        ->assertJsonPath('data.1.code', 'USD');
});

it('AC-001-02: requires authentication', function (): void {
    $this->getJson('/api/v1/currencies')
        ->assertUnauthorized()
        ->assertHeader('Content-Type', 'application/problem+json');
});
