<?php

declare(strict_types=1);

namespace Src\Currency\Application\ConvertAmount;

use Src\Currency\Domain\Currency;
use Src\Currency\Domain\CurrencyConverter;
use Src\Currency\Domain\CurrencyRepository;
use Src\Currency\Domain\Exceptions\CurrencyNotFound;
use Src\Currency\Domain\ValueObjects\CurrencyId;
use Src\Shared\Domain\ValueObjects\Amount;

/**
 * Public entry point of the Currency context for other contexts (ADR-0009):
 * primitives in, primitives out, so consumers never import Currency\Domain.
 */
final readonly class ConvertAmountUseCase
{
    public function __construct(
        private CurrencyRepository $currencies,
        private CurrencyConverter $converter,
    ) {}

    /**
     * @return string the converted amount with 2 decimals, e.g. "92.00"
     *
     * @throws CurrencyNotFound
     */
    public function execute(ConvertAmountCommand $command): string
    {
        return $this->converter->convert(
            Amount::fromString($command->amount),
            $this->currency($command->fromCurrencyId),
            $this->currency($command->toCurrencyId),
        )->value();
    }

    private function currency(int $id): Currency
    {
        return $this->currencies->find(new CurrencyId($id)) ?? throw CurrencyNotFound::withId($id);
    }
}
