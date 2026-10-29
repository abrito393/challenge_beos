<?php

declare(strict_types=1);

namespace Src\Catalog\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Src\Catalog\Application\CreateProductPrice\CreateProductPriceCommand;

final class CreateProductPriceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Ability is enforced by the route (abilities:products:write).
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'currency_id' => ['required', 'integer', 'exists:currencies,id'],
            // Optional: when omitted it is computed from the base price and exchange rates.
            'price' => ['sometimes', 'nullable', 'numeric', 'min:0', 'decimal:0,2', 'max:9999999999999.99'],
        ];
    }

    public function toCommand(int $productId): CreateProductPriceCommand
    {
        return new CreateProductPriceCommand(
            productId: $productId,
            currencyId: $this->integer('currency_id'),
            price: $this->filled('price') ? $this->string('price')->toString() : null,
        );
    }
}
