<?php

declare(strict_types=1);

namespace Shopie\DiContainer;

use Shopie\DiContainer\Contracts\ServiceProviderInterface;

abstract class ServiceProviderBase implements ServiceProviderInterface
{
    abstract public function getService(string $abstractOrConcrete): ?object;
}