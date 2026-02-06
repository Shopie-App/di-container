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
     * plus a concrete type. Type of services are Scoped and Ephemeral. 
     * Ephemeral services will always return a new object.
     * 
     * @param string $abstractOrConcrete Abstract or concrete fully qualified class name.
     * @param null|string|callable $concrete Concrete type fully qualified class name or a Closure.
     * @param int $type Type of service. Set to self::TYPE_SCOPED or self::TYPE_EPHEMERAL.
     *                  Defaults to Scoped.
     */
    public function add(string $abstractOrConcrete, string|callable|null $concrete = null, int $type = self::TYPE_SCOPED): void;

    /**
     * Checks if a specific service exists in the collection.
     * 
     * @param string $abstractOrConcrete Abstract or concrete fully qualified class name.
     */
    public function exists(string $abstractOrConcrete): bool;

    /**
     * Get a service called from provider.
     * 
     * @param string $abstractOrConcrete Abstract or concrete fully qualified class name.
     * 
     * @return null|Service Returns a Service object or null if not in collection.
     */
    public function get(string $abstractOrConcrete): ?Service;

    /**
     * Removes a service from the collection.
     * 
     * @param string $abstractOrConcrete Abstract or concrete fully qualified class name.
     */
    public function remove(string $abstractOrConcrete): void;

    /**
     * Sets the instantiated object for a service.
     * 
     * @param string $abstractOrConcrete Abstract or concrete fully qualified class name.
     * @param object $instance The instantiated object.
     */
    public function setObject(string $abstractOrConcrete, object $instance): void;
}