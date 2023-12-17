<?php

declare(strict_types=1);

namespace Shopie\DiContainer\Contracts;

interface ServiceProviderInterface
{
    /**
     * Instantiates object and all of its dependent objects.
     * 
     * @param string $abstractOrConcrete Abstract/Interface or Concrete type fully qualified name.
     * 
     * @return object|null Returns instantiated object or null.
     */
    public function getService(string $abstractOrConcrete): ?object;
}