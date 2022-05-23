#!/bin/bash

# shellcheck disable=SC2034
INI=$(cat <<END
<?php

declare(strict_types=1);

use Cache\Adapter\Redis\RedisCachePool;

return [
    'cache' => [
        'cache-pool-class' => RedisCachePool::class,
        'primary-endpoint' => '${REDIS_ADDRESS}',
    ],
];

END
)

echo "$${INI}" > /tmp/cache.local.php
sudo chown www-data.www-data /tmp/cache.local.php
sudo mv /tmp/cache.local.php /var/local/app/config/autoload/cache.local.php
