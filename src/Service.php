<?php

declare(strict_types=1);

namespace Shopie\DiContainer;

final class Service
{
    public function __construct(
        public readonly string $concreteClassName,
        public readonly int $type,
        public readonly int $collectionPos,
        public ?object $instance = null
    ) {
    }
}