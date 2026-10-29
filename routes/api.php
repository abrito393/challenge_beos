<?php

declare(strict_types=1);

/*
| Composition root of the `api` group (prefix: api/v1). Each bounded context
| owns its routes in src/<Context>/Infrastructure/Http/routes.php.
*/

require base_path('src/Auth/Infrastructure/Http/routes.php');
require base_path('src/Currency/Infrastructure/Http/routes.php');
require base_path('src/Catalog/Infrastructure/Http/routes.php');
