<?php

declare(strict_types=1);

namespace Shopie\DiContainer;

use ReflectionClass;
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
            if ($service->type === ServiceCollectionInterface::TYPE_SCOPED && $service->instance !== null) {
                return $service->instance;
            }

            // start introspection
            $reflector = new ReflectionClass($service->concreteClassName);

            // stop if abstract or interface
            if (!$reflector->isInstantiable()) {
                throw new ServiceProvideException('Class '.$service->concreteClassName.' cannot be instantiated (abstract or interface)');
            }
            
            // get constructor
            $classConstructor = $reflector->getConstructor();

            $args = [];

            if ($classConstructor !== null) {
                
                // get any params in constructor
                $params = $classConstructor->getParameters();

                foreach ($params as $param) {

                    // get type, handle null
                    if (($reflectedType = $param->getType()) === null) {
                        $args[] = null;
                        continue;
                    }

                    // resolve the type to inject
                    $typeName = null;

                    if ($reflectedType instanceof ReflectionUnionType) {
                        foreach ($reflectedType->getTypes() as $type) {
                            if (!$type->isBuiltin()) {
                                $typeName = $type->getName();
                                break;
                            }
                        }
                    } elseif ($reflectedType instanceof \ReflectionNamedType) {
                        if (!$reflectedType->isBuiltin()) {
                            $typeName = $reflectedType->getName();
                        }
                    }

                    // get and init service
                    $args[] = $typeName !== null && !$param->isDefaultValueAvailable()
                        ? $this->getService($typeName)
                        : $param->getDefaultValue();
                }
            }

            // init object with args
            $object = $reflector->newInstance(...$args);

            // if scoped add reference to collection
            if ($service->type === ServiceCollectionInterface::TYPE_SCOPED) {
                $this->collection->setObject($service->abstractKey, $object);
            }

            // return object
            return $object;

        } catch (\ReflectionException $ex) {

            throw new ServiceProvideException($ex->getMessage());
        }
    }
}