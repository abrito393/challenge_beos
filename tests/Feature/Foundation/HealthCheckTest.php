<?php

declare(strict_types=1);

it('AC-000-01: exposes a health check endpoint', function (): void {
    $this->get('/up')->assertOk();
});
