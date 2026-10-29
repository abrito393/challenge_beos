<?php

declare(strict_types=1);

use Src\Catalog\Application\CreateProduct\CreateProductCommand;
use Src\Catalog\Application\CreateProduct\CreateProductUseCase;
use Src\Catalog\Application\DeleteProduct\DeleteProductCommand;
use Src\Catalog\Application\DeleteProduct\DeleteProductUseCase;
use Src\Catalog\Application\GetProduct\GetProductQuery;
use Src\Catalog\Application\GetProduct\GetProductUseCase;
use Src\Catalog\Application\ListProducts\ListProductsQuery;
use Src\Catalog\Application\ListProducts\ListProductsUseCase;
use Src\Catalog\Application\ListProducts\ProductSearcher;
use Src\Catalog\Application\ListProducts\ProductSort;
use Src\Catalog\Application\UpdateProduct\UpdateProductCommand;
use Src\Catalog\Application\UpdateProduct\UpdateProductUseCase;
use Src\Catalog\Domain\Exceptions\InvalidProductName;
use Src\Catalog\Domain\Exceptions\ProductNotFound;
use Src\Catalog\Domain\ValueObjects\ProductId;
use Src\Shared\Application\Page;
use Src\Shared\Domain\Exceptions\InvalidAmount;
use Tests\Unit\Support\InMemoryProductRepository;

beforeEach(function (): void {
    $this->store = new InMemoryProductRepository;
    $this->create = new CreateProductUseCase($this->store, $this->store->finder());
});

function createCommand(array $overrides = []): CreateProductCommand
{
    return new CreateProductCommand(...[
        'name' => 'Laptop', 'description' => '14 inch', 'price' => '1299.99',
        'currencyId' => 1, 'taxCost' => '207.99', 'manufacturingCost' => '650', ...$overrides,
    ]);
}

it('AC-002-01: creates a product and returns its read model with exact amounts', function (): void {
    $view = $this->create->execute(createCommand());

    expect($view->id)->toBe(1)
        ->and($view->price)->toBe('1299.99')
        ->and($view->manufacturingCost)->toBe('650.00')
        ->and($view->currency->id)->toBe(1)
        ->and($this->store->products)->toHaveCount(1);
});

it('AC-002-02: refuses to create a product that breaks a domain invariant', function (array $overrides, string $exception): void {
    expect(fn () => $this->create->execute(createCommand($overrides)))->toThrow($exception)
        ->and($this->store->products)->toBeEmpty();
})->with([
    'blank name' => [['name' => ' '], InvalidProductName::class],
    'negative price' => [['price' => '-1'], InvalidAmount::class],
]);

it('AC-002-12: updates every field of an existing product', function (): void {
    $id = $this->create->execute(createCommand())->id;

    $view = (new UpdateProductUseCase($this->store, $this->store->finder()))->execute(new UpdateProductCommand(
        productId: $id, name: 'Laptop Pro', description: '16 inch', price: '1999', currencyId: 2, taxCost: '1', manufacturingCost: '2',
    ));

    expect($view->name)->toBe('Laptop Pro')
        ->and($view->price)->toBe('1999.00')
        ->and($view->currency->id)->toBe(2);
});

it('AC-002-11: fails with ProductNotFound for unknown products', function (string $action): void {
    $run = match ($action) {
        'get' => fn () => (new GetProductUseCase($this->store->finder()))->execute(new GetProductQuery(404)),
        'update' => fn () => (new UpdateProductUseCase($this->store, $this->store->finder()))
            ->execute(new UpdateProductCommand(404, 'x', 'y', '1', 1, '0', '0')),
        'delete' => fn () => (new DeleteProductUseCase($this->store))->execute(new DeleteProductCommand(404)),
    };

    expect($run)->toThrow(ProductNotFound::class, 'Product 404 was not found.');
})->with(['get', 'update', 'delete']);

it('AC-002-14: deletes an existing product and AC-002-15: a second delete is not found', function (): void {
    $id = $this->create->execute(createCommand())->id;
    $delete = new DeleteProductUseCase($this->store);

    $delete->execute(new DeleteProductCommand($id));

    expect($this->store->find(new ProductId($id)))->toBeNull()
        ->and(fn () => $delete->execute(new DeleteProductCommand($id)))->toThrow(ProductNotFound::class);
});

it('AC-002-10: gets a product read model', function (): void {
    $id = $this->create->execute(createCommand())->id;

    expect((new GetProductUseCase($this->store->finder()))->execute(new GetProductQuery($id))->name)->toBe('Laptop');
});

it('delegates listing to the searcher port', function (): void {
    $searcher = new class implements ProductSearcher
    {
        public ?ListProductsQuery $received = null;

        public function search(ListProductsQuery $query): Page
        {
            $this->received = $query;

            return new Page([], 0, $query->perPage, $query->page);
        }
    };
    $query = new ListProductsQuery(name: 'lap', sort: ProductSort::PriceDesc, perPage: 5);

    $page = (new ListProductsUseCase($searcher))->execute($query);

    expect($searcher->received)->toBe($query)->and($page->perPage)->toBe(5);
});

it('AC-002-07: exposes whitelisted sort options with field and direction', function (): void {
    expect(ProductSort::values())->toBe(['name', '-name', 'price', '-price', 'created_at', '-created_at'])
        ->and(ProductSort::PriceDesc->field())->toBe('price')
        ->and(ProductSort::PriceDesc->isDescending())->toBeTrue()
        ->and(ProductSort::NameAsc->isDescending())->toBeFalse()
        ->and(ProductSort::DEFAULT)->toBe(ProductSort::CreatedAtDesc);
});
