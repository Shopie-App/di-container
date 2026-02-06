<?php

declare(strict_types=1);

namespace Shopie\DiContainer;

use ReflectionClass;
use ReflectionUnionType;
use ReflectionNamedType;
use Shopie\DiContainer\Contracts\ServiceCollectionInterface;
use Shopie\DiContainer\Exception\ServiceNotInContainerException;
use Shopie\DiContainer\Exception\ServiceProviderException;

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

            // if concrete is a closure/callable, execute it and return service instance
            if (is_callable($service->concrete)) {
                
                $object = ($service->concrete)($this);

                if ($service->type === ServiceCollectionInterface::TYPE_SCOPED) {
                    $this->collection->setObject($service->abstraction, $object);
                }

                return $object;
            }

            // ensure concrete is a string (class name) before reflection
            if (!is_string($service->concrete)) {
                throw new ServiceProviderException('Service concrete type for '.$service->abstraction.' must be a string or callable, '.gettype($service->concrete).' given.');
            }

            // start introspection
            $reflector = new ReflectionClass($service->concrete);

            // stop if abstract or interface
            if (!$reflector->isInstantiable()) {
                if ($service->concrete === $service->abstraction) {
                    throw new ServiceProviderException('Service "' . $service->abstraction . '" is not instantiable. It is likely an interface or abstract class registered without a concrete implementation.');
                }

                throw new ServiceProviderException('Service '.$service->concrete.' cannot be instantiated (abstract or interface)');
            }
            
            // get constructor
            $classConstructor = $reflector->getConstructor();

            $args = [];

            if ($classConstructor !== null) {
                
                // get any params in constructor
                $params = $classConstructor->getParameters();

                foreach ($params as $param) {
                    
                    $reflectedType = $param->getType();

                    $typeName = $this->resolveTypeName($reflectedType);

                    // 1. Try to resolve class dependency
                    if ($typeName !== null) {
                        // if registered, inject it
                        if ($this->collection->exists($typeName)) {
                            $args[] = $this->getService($typeName);
                            continue;
                        }
                        
                        // if not registered but has default, use default
                        if ($param->isDefaultValueAvailable()) {
                            $args[] = $param->getDefaultValue();
                            continue;
                        }

                        // mandatory but not found -> let getService throw exception
                        $args[] = $this->getService($typeName);
                        continue;
                    }

                    // 2. Handle primitives / built-ins / untyped
                    if ($param->isDefaultValueAvailable()) {
                        $args[] = $param->getDefaultValue();
                        continue;
                    }

                    // 3. Handle nullable primitives without default (e.g. ?int $x)
                    if ($reflectedType !== null && $reflectedType->allowsNull()) {
                        $args[] = null;
                        continue;
                    }

                    // 4. Fail
                    throw new ServiceProviderException(sprintf(
                        'Parameter "%s" in class "%s" cannot be resolved. It has no type hint or is a built-in type with no default value.',
                        $param->getName(),
                        $service->concrete
                    ));
                }
            }

            // init object with args
            $object = $reflector->newInstanceArgs($args);

            // if scoped add reference to collection
            if ($service->type === ServiceCollectionInterface::TYPE_SCOPED) {
                $this->collection->setObject($service->abstraction, $object);
            }

            // return object
            return $object;

        } catch (\ReflectionException $ex) {

            throw new ServiceProviderException($ex->getMessage());
        }
    }

    private function resolveTypeName(?\ReflectionType $type): ?string
    {
        if ($type instanceof ReflectionUnionType) {
            foreach ($type->getTypes() as $innerType) {
                if (!$innerType->isBuiltin()) {
                    return $innerType->getName();
                }
            }
        } elseif ($type instanceof ReflectionNamedType) {
            if (!$type->isBuiltin()) {
                return $type->getName();
            }
        }

        return null;
    }
}