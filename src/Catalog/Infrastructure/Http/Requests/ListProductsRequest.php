<?php

declare(strict_types=1);

namespace Src\Catalog\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\In;
use Src\Catalog\Application\ListProducts\ListProductsQuery;
use Src\Catalog\Application\ListProducts\ProductSort;

final class ListProductsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Ability is enforced by the route (abilities:products:read).
    }

    /** @return array<string, list<string|In>> */
    public function rules(): array
    {
        return [
            // Unknown filter keys are rejected instead of silently ignored.
            'filter' => ['sometimes', 'array:name,currency_id,min_price,max_price'],
            'filter.name' => ['sometimes', 'string', 'max:255'],
            'filter.currency_id' => ['sometimes', 'integer', 'min:1'],
            'filter.min_price' => ['sometimes', 'numeric', 'min:0'],
            'filter.max_price' => ['sometimes', 'numeric', 'min:0', 'gte:filter.min_price'],
            'sort' => ['sometimes', 'string', Rule::in(ProductSort::values())],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:'.ListProductsQuery::MAX_PER_PAGE],
        ];
    }

    public function toQuery(): ListProductsQuery
    {
        return new ListProductsQuery(
            name: $this->filled('filter.name') ? $this->string('filter.name')->toString() : null,
            currencyId: $this->filled('filter.currency_id') ? $this->integer('filter.currency_id') : null,
            minPrice: $this->filled('filter.min_price') ? $this->string('filter.min_price')->toString() : null,
            maxPrice: $this->filled('filter.max_price') ? $this->string('filter.max_price')->toString() : null,
            sort: ProductSort::from($this->string('sort', ProductSort::DEFAULT->value)->toString()),
            page: $this->integer('page', 1),
            perPage: $this->integer('per_page', ListProductsQuery::DEFAULT_PER_PAGE),
        );
    }
}
