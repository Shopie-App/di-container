<?php

declare(strict_types=1);

namespace Shopie\DiContainer;

use Shopie\DiContainer\Contracts\ServiceContainerInterface;

abstract class ServiceContainerBase implements ServiceContainerInterface
{
    abstract public function addScoped(string $abstractOrConcrete, string|callable|null $concrete = null): void;

    abstract public function addEphemeral(string $abstractOrConcrete, string|callable|null $concrete = null): void;

    abstract public function resetAll(): void;
}