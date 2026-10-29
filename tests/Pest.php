<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
| Feature tests boot the framework and run against the isolated MySQL testing
| database (AC-000-02). Unit tests stay framework-free: they exercise the pure
| domain and application layers with in-memory adapters.
*/
pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');
