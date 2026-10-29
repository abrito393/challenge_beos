<?php

declare(strict_types=1);

return [
    /*
    | ISO 4217 code of the reference currency. Every exchange_rate expresses
    | units of a currency per 1 unit of this one, so it must be 1 (ADR-0004).
    */
    'reference' => env('CURRENCY_REFERENCE', 'USD'),
];
