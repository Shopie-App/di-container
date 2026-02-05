<?php

declare(strict_types=1);

namespace Shopie\DiContainer;

use Shopie\DiContainer\Contracts\ServiceCollectionInterface;
use Shopie\DiContainer\Exception\NoConcreteTypeException;
use Shopie\DiContainer\Exception\ServiceInCollectionException;

final class ServiceCollection implements ServiceCollectionInterface
{
    public int $count {
        get => count($this->collection);
    }

    public function __construct(
        private array $collection = [],
        private array $aliases = []
    ) {}

    public function add(string $abstractOrConcrete, ?string $concrete = null, int $type = self::TYPE_SCOPED, ?object $object = null): void
    {
        // is concrete flag when no concrete is send
        $isConcrete = $this->isConcrete($abstractOrConcrete);

        // stop if no concrete has been send
        if ($concrete == null && !$isConcrete) {
            throw new NoConcreteTypeException($abstractOrConcrete);
        }

        // already in collection?
        if ($this->exists($abstractOrConcrete)) {
            throw new ServiceInCollectionException($abstractOrConcrete);
        }

        // add
        $this->collection[$abstractOrConcrete] = [
            'concrete' => $concrete ?? $abstractOrConcrete,
            'type' => $type,
            'instance' => $object
        ];

        // create an alias if a different concrete name exists
        if ($concrete !== null && $concrete !== $abstractOrConcrete) {
            $this->aliases[$concrete] = $abstractOrConcrete;
        }
    }

    public function exists(string $abstractOrConcrete): bool
    {
        return isset($this->collection[$abstractOrConcrete]) || isset($this->aliases[$abstractOrConcrete]);
    }

    public function get(?string $abstractOrConcrete): ?Service
    {
        // resolve alias first
        $key = $this->aliases[$abstractOrConcrete] ?? $abstractOrConcrete;

        if (!isset($this->collection[$key])) {
            return null;
        }

        // fetch data
        $data = $this->collection[$key];

        // return new service
        return new Service(
            $data['concrete'],
            $data['type'],
            $key,
            $data['instance']
        );
    }

    public function remove(string $abstractOrConcrete): void
    {
        // resolve the real key (works if input is Alias or Abstract)
        $key = $this->aliases[$abstractOrConcrete] ?? $abstractOrConcrete;

        // remove the service definition
        unset($this->collection[$key]);

        // remove the input itself from aliases if it was an alias 
        // (this covers the case where the key IS the input)
        unset($this->aliases[$abstractOrConcrete]);

        // remove ALL aliases pointing to this service
        $aliasesToRemove = array_keys($this->aliases, $key, true);

        foreach ($aliasesToRemove as $alias) {
            unset($this->aliases[$alias]);
        }
    }

    public function setObject(string $abstractOrConcrete, object $instance): void
    {
        $key = $this->aliases[$abstractOrConcrete] ?? $abstractOrConcrete;

        if (isset($this->collection[$key])) {
            $this->collection[$key]['instance'] = $instance;
        }
    }

    private function isConcrete(string $className): bool
    {
        $reflector = new \ReflectionClass($className);

        return !$reflector->isAbstract() && !$reflector->isInterface();
    }
}