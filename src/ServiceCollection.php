<?php

declare(strict_types=1);

namespace Shopie\DiContainer;

use Shopie\DiContainer\Contracts\ResettableInterface;
use Shopie\DiContainer\Contracts\ServiceCollectionInterface;
use Shopie\DiContainer\Exception\ServiceInCollectionException;

final class ServiceCollection implements ServiceCollectionInterface
{
    public private(set) array $resettableInstances = [];

    public int $count {
        get => count($this->collection);
    }

    public function __construct(
        private array $collection = [],
        private array $aliases = []
    ) {}

    public function add(string $abstractOrConcrete, string|callable|null $concrete = null, int $type = self::TYPE_SCOPED): void
    {
        // already in collection?
        if ($this->exists($abstractOrConcrete)) {
            throw new ServiceInCollectionException($abstractOrConcrete);
        }

        // add
        $this->collection[$abstractOrConcrete] = [
            'concrete' => $concrete ?? $abstractOrConcrete,
            'type' => $type,
            'instance' => null
        ];

        // create an alias if a different concrete name exists
        if (is_string($concrete) && $concrete !== $abstractOrConcrete) {
            $this->aliases[$concrete] = $abstractOrConcrete;
        }
    }

    public function exists(string $abstractOrConcrete): bool
    {
        return isset($this->collection[$abstractOrConcrete]) || isset($this->aliases[$abstractOrConcrete]);
    }

    public function get(string $abstractOrConcrete): ?Service
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
            $key,
            $data['concrete'],
            $data['type'],
            $data['instance']
        );
    }

    public function remove(string $abstractOrConcrete): void
    {
        // resolve the real key (works if input is Alias or Abstract)
        $key = $this->aliases[$abstractOrConcrete] ?? $abstractOrConcrete;

        // cleanup resettable instance if it exists
        if (isset($this->collection[$key]['instance'])) {
            $instance = $this->collection[$key]['instance'];
            if ($instance instanceof ResettableInterface) {
                unset($this->resettableInstances[spl_object_hash($instance)]);
            }
        }

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

    public function resetAll(): void
    {
        foreach ($this->resettableInstances as $service) {
            $service->reset();
        }
    }

    public function setObject(string $abstractOrConcrete, object $instance): void
    {
        $key = $this->aliases[$abstractOrConcrete] ?? $abstractOrConcrete;

        // store the instance
        if (isset($this->collection[$key])) {
            // cleanup old instance if it exists
            if (($oldInstance = $this->collection[$key]['instance']) instanceof ResettableInterface) {
                unset($this->resettableInstances[spl_object_hash($oldInstance)]);
            }

            $this->collection[$key]['instance'] = $instance;

            // track it for resetting if it implements the interface
            if ($instance instanceof ResettableInterface) {
                $this->resettableInstances[spl_object_hash($instance)] = $instance;
            }
        }
    }
}