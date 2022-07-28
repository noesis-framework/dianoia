<?php

declare(strict_types=1);

$root = dirname(__DIR__);
return [
    'env' => 'test',
    'cache' => [
        'container' => [
            'compiled' => "$root/tmp/cache/container/compiled",
            'definition' => "$root/tmp/cache/container/definition"
        ]
    ]
];
