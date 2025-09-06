<?php

declare(strict_types=1);

namespace Shopie\DiContainer;

use Shopie\DiContainer\Contracts\ServiceCollectionInterface;
use Shopie\DiContainer\Exception\NoConcreteTypeException;

final class ServiceCollection implements ServiceCollectionInterface
{
    public function __construct(
        private array $collection = []
    ) {}

    public function add(string $abstractOrConcrete, ?string $concrete = null, int $type = 1, ?object $object = null): void
    {
        // is concrete flag when no concrete is send
        $isConcrete = $this->isConcrete($abstractOrConcrete);

        // stop if no concrete has been send
        if ($concrete == null && !$isConcrete) {
            throw new NoConcreteTypeException($abstractOrConcrete);
        }

        // already in collection?
        if ($this->exists($isConcrete, $abstractOrConcrete, $concrete)) {
            return;
        }

        // add with no abstract type
        if ($concrete == null) {

            $this->collection[] = [null, $abstractOrConcrete, $type, $object];
            return;
        }

        // add with abstract type
        $this->collection[] = [$abstractOrConcrete, $concrete, $type, $object];
    }

    public function exists(bool $isConcrete, string $abstractOrConcrete, ?string $concrete = null): bool
    {
        if (($len = count($this->collection)) == 0) {
            return false;
        }

        $exists = false;

        $searchPos = $concrete != null ? 1 : ($isConcrete ? 1 : 0);

        $searchKey = $concrete != null ? $concrete : $abstractOrConcrete;

        for ($i = 0; $i < $len; $i++) {

            if ($this->collection[$i][$searchPos] == $searchKey) {

                $exists = true;
                break;
            }
        }

        return $exists;
    }

    public function get(?string $abstractOrConcrete): ?Service
    {
        // length of collection, return if empty
        if (($len = count($this->collection)) == 0) {
            return null;
        }
        // is concrete defines element's position to search for in item
        $searchPos = $this->isConcrete($abstractOrConcrete) ? 1 : 0;

        $service = null;

        // position in collection
        $pos = 0;

        for ($i = 0; $i < $len; $i++) {

            if ($this->collection[$i][$searchPos] === $abstractOrConcrete) {

                // service data object
                $service = new Service(
                    $this->collection[$i][1],
                    $this->collection[$i][2],
                    $pos,
                    $this->collection[$i][3]
                );
                break;
            }

            $pos++;
        }

        return $service;
    }

    public function size(): int
    {
        return count($this->collection);
    }

    public function remove(int $pos): void
    {
        if ($this->collection[$pos][3] !== null) {
            $this->collection[$pos][3] = null;
        }
        
        array_splice($this->collection, $pos, 1);
    }

    public function setObject(int $pos, object $objectId): void
    {
        $this->collection[$pos][3] = $objectId;
    }

    private function isConcrete(string $className): bool
    {
        $reflector = new \ReflectionClass($className);

        return !$reflector->isAbstract() && !$reflector->isInterface();
    }
}