<?php

declare(strict_types=1);

namespace Shopie\DiContainer;

use Shopie\DiContainer\Contracts\ServiceCollectionInterface;

final class ServiceContainer extends ServiceContainerBase
{
    public function __construct(private ServiceCollectionInterface $collection)
    {
    }

    public function addScoped(string $abstractOrConcrete, ?string $concrete = null): void
    {
        $this->collection->add($abstractOrConcrete, $concrete, 1);
    }

    public function addEphemeral(string $abstractOrConcrete, ?string $concrete = null): void
    {
        $this->collection->add($abstractOrConcrete, $concrete, 2);
    }
}