<?php

declare(strict_types=1);

namespace Src\Shared\Application;

/**
 * Framework-agnostic page of results returned by read ports.
 *
 * @template T
 */
final readonly class Page
{
    /**
     * @param  list<T>  $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $perPage,
        public int $currentPage,
    ) {}
}
