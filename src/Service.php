<?php

declare(strict_types=1);

namespace Shopie\DiContainer;

final class Service
{
    /**
     * @param string $abstraction The unique identifier for the service (usually an Interface or Abstract class name).
     * @param mixed $concrete The concrete implementation. Can be a class name string or a Closure.
     * @param int $type The lifecycle type of the service (e.g., Scoped or Ephemeral).
     * @param object|null $instance The resolved instance of the service (if Scoped and already resolved).
     */
    public function __construct(
        public readonly string $abstraction,
        public readonly mixed $concrete,
        public readonly int $type,
        public readonly ?object $instance = null
    ) {
    }
}