<?php

declare(strict_types=1);

namespace Shopie\DiContainer\Contracts;

interface ServiceContainerInterface
{
    /**
     * Adds a scoped service.
     * 
     * @param string $abstractOrConcrete Abstract/Interface or Concrete type fully qualified name.
     * @param string $concrete Concrete type fully qualified name or null if 1st param is concrete.
     * 
     * @return void
     */
    public function addScoped(string $abstractOrConcrete, ?string $concrete = null): void;

    /**
     * Adds a transient service.
     * 
     * @param string $abstractOrConcrete Abstract/Interface or Concrete type fully qualified name.
     * @param string $concrete Concrete type fully qualified name or null if 1st param is concrete.
     * 
     * @return void
     */
    public function addEphemeral(string $abstractOrConcrete, ?string $concrete = null): void;
}