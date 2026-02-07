<?php

declare(strict_types=1);

namespace Shopie\DiContainer\Contracts;

interface ServiceContainerInterface
{
    /**
     * Adds a scoped service.
     * 
     * @param string $abstractOrConcrete Abstract/Interface or Concrete type fully qualified name.
     * @param null|string|callable $concrete Concrete type fully qualified class name or a Closure or null if 1st param is concrete.
     * 
     * @return void
     */
    public function addScoped(string $abstractOrConcrete, string|callable|null $concrete = null): void;

    /**
     * Adds a transient service.
     * 
     * @param string $abstractOrConcrete Abstract/Interface or Concrete type fully qualified name.
     * @param null|string|callable $concrete Concrete type fully qualified class name or a Closure or null if 1st param is concrete.
     * 
     * @return void
     */
    public function addEphemeral(string $abstractOrConcrete, string|callable|null $concrete = null): void;

    /**
     * Resets all instantiated services that implement ResettableInterface.
     *
     * @return void
     */
    public function resetAll(): void;
}