<?php

declare(strict_types=1);

namespace Shopie\DiContainer\Contracts;

use Shopie\DiContainer\Service;

interface ServiceCollectionInterface
{
    /**
     * Service Types
     */
    public const TYPE_SCOPED = 1;
    public const TYPE_EPHEMERAL = 2;

    /**
     * Total number of services currently registered in the collection.
     */
    public int $count { get; }

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
     * @param int $type Type of service. Set to self::TYPE_SCOPED or self::TYPE_EPHEMERAL.
     *                  Defaults to Scoped.
     * @param null|object $object Instantiated object from container.
     */
    public function add(string $abstractOrConcrete, ?string $concrete = null, int $type = self::TYPE_SCOPED, ?object $object = null): void;

    /**
     * Checks if a specific service exists in the collection.
     * 
     * @param string $abstractOrConcrete Abstract or concrete fully qualified class name.
     */
    public function exists(string $abstractOrConcrete): bool;

    /**
     * Get a service called from provider.
     * 
     * @param null|string $abstractOrConcrete Abstract or concrete fully qualified class name.
     * 
     * @return null|Service Returns a Service object or null if not in collection.
     */
    public function get(?string $abstractOrConcrete): ?Service;

    /**
     * TODO: 2 below need tests
     */
    public function remove(string $abstractOrConcrete): void;

    public function setObject(string $abstractOrConcrete, object $objectId): void;
}