<?php

declare(strict_types=1);

namespace Src\Catalog\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Src\Catalog\Application\CreateProduct\CreateProductCommand;

final class CreateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Ability is enforced by the route (abilities:products:write).
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        $money = ['required', 'numeric', 'min:0', 'decimal:0,2', 'max:9999999999999.99'];

        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
            'price' => $money,
            'currency_id' => ['required', 'integer', 'exists:currencies,id'],
            'tax_cost' => $money,
            'manufacturing_cost' => $money,
        ];
    }

    public function toCommand(): CreateProductCommand
    {
        return new CreateProductCommand(
            name: $this->string('name')->toString(),
            description: $this->string('description')->toString(),
            price: $this->string('price')->toString(),
            currencyId: $this->integer('currency_id'),
            taxCost: $this->string('tax_cost')->toString(),
            manufacturingCost: $this->string('manufacturing_cost')->toString(),
        );
    }
}
