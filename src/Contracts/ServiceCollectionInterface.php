<?php

declare(strict_types=1);

namespace Shopie\DiContainer\Contracts;

use Shopie\DiContainer\Service;

interface ServiceCollectionInterface
{
    /**
     * Add a service to the collection.
     * 
     * Add a service either by providing a concrete type or an abstract type 
     * plus a concrete type.Type of services are Scoped and Ephemeral. 
     * Parameter $object is the instantiated object. Ephemeral services will always 
     * return a new object.
     * 
     * @param string $abstractOrConcrete Abstract or concrete fully qualified class name. 
     *                                  Throws a NoConcreteTypeException exception when $concrete 
     *                                  is not provided and $abstractOrConcrete is an abstraction.
     * @param null|string $concrete Concrete type fully qualified class name.
     * @param int $type Type of service. Set to 1 for Scoped services or to 2 for Ephemeral ones.
     *                  Defaults to Scoped.
     * @param null|object $object Instantiated object from container.
     */
    public function add(string $abstractOrConcrete, ?string $concrete = null, int $type = 1, ?object $object = null): void;

    
    public function exists(bool $isConcrete, string $abstractOrConcrete, ?string $concrete = null): bool;

    /**
     * Get a service called from provider.
     * 
     * @param null|string $abstractOrConcrete Abstract or concrete fully qualified class name.
     * 
     * @return null|Service Returns a Service object or null if not in collection.
     */
    public function get(?string $abstractOrConcrete): ?Service;

    /**
     * Get the size of the collection.
     */
    public function size(): int;

    /**
     * TODO: 2 below need tests
     */
    public function remove(int $pos): void;

    public function setObject(int $pos, object $objectId): void;
}