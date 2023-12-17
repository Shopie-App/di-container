<?php

declare(strict_types=1);

namespace Shopie\DiContainer;

use ReflectionClass;
use ReflectionNamedType;
use ReflectionUnionType;
use Shopie\DiContainer\Contracts\ServiceCollectionInterface;
use Shopie\DiContainer\Exception\ServiceNotInContainerException;
use Shopie\DiContainer\Exception\ServiceProvideException;

final class ServiceProvider extends ServiceProviderBase
{
    public function __construct(private ServiceCollectionInterface $collection)
    {
    }

    public function getService(string $abstractOrConcrete): object
    {
        // get service data
        if (($service = $this->collection->get($abstractOrConcrete)) === null) {
            throw new ServiceNotInContainerException($abstractOrConcrete);
        }

        // return instance
        return $this->initService($service);
    }

    private function initService(Service $service): ?object
    {
        try {

            // if scoped service check for instance and return it
            if ($service->type === 1 && $service->instance !== null) {
                return $service->instance;
            }

            // start introspection
            $reflector = new ReflectionClass($service->concreteClassName);

            // stop if abstract
            if ($reflector->isAbstract()) {
                throw new ServiceProvideException('Abstract class '.$service->concreteClassName.' cannot be instantiated');
            }

            // stop if interface
            if ($reflector->isInterface()) {
                throw new ServiceProvideException('Interface type '.$service->concreteClassName.' cannot be instantiated');
            }
            
            // get constructor
            $classConstructor = $reflector->getConstructor();

            // if no constructor add instance to collection and return it
            if ($classConstructor === null) {

                $object = $reflector->newInstanceWithoutConstructor();

                if ($service->type == 1) {
                    $this->collection->setObject($service->collectionPos, $object);
                }

                return $object;
            }

            // get any params in constructor
            $params = $classConstructor->getParameters();

            // constructor loaded arguments
            $args = [];

            foreach ($params as $param) {

                // get type, handle null
                if (($reflectedType = $param->getType()) === null) {

                    $args[] = null;
                    continue;
                }

                // case of union or single declared type
                $types = $reflectedType instanceof ReflectionUnionType ? $reflectedType->getTypes() : [$reflectedType];

                // get and init service
                $args[] = !$types[0]->isBuiltin() && !$param->isDefaultValueAvailable() 
                ? $this->getService($types[0]->getName()) : $param->getDefaultValue();
            }

            // init object with args
            $object = $this->newInstance($reflector, $args);

            // if scoped add reference to collection
            if ($service->type == 1) {
                $this->collection->setObject($service->collectionPos, $object);
            }

            $service = null;

            // return object
            return $object;

        } catch (\ReflectionException $ex) {

            throw new ServiceProvideException($ex->getMessage());
        }
    }

    private function newInstance(ReflectionClass $reflector, array $args): object
    {
        return $reflector->newInstance(...$args);
    }
}