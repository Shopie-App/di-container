<?php

declare(strict_types=1);

namespace Shopie\DiContainer\Contracts;

/**
 * Interface for services that maintain state and require resetting.
 *
 * In persistent application environments (e.g., FrankenPHP, RoadRunner, Swoole),
 * services are often reused across multiple requests. Implementing this interface
 * allows the application to reset the service's state (such as clearing user identity,
 * tenant context, or database connections) after each request to prevent data leakage.
 */
interface ResettableInterface
{
    /**
     * Resets the service to its initial state.
     */
    public function reset(): void;
}