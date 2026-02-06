<?php

declare(strict_types=1);

namespace Shopie\DiContainer\Tests;

use PHPUnit\Framework\TestCase;
use Shopie\DiContainer\Contracts\ServiceCollectionInterface;
use Shopie\DiContainer\Exception\ServiceNotInContainerException;
use Shopie\DiContainer\ServiceCollection;
use Shopie\DiContainer\ServiceProvider;
use Shopie\DiContainer\Exception\ServiceProviderException;

final class ServiceProviderTest extends TestCase
{
    /**
     * Test service not in container exception thrown.
     */
    public function testServiceProviderNotFoundException(): void
    {
        // init collection and provider
        $provider = new ServiceProvider(new ServiceCollection());

        // register
        $this->expectException(ServiceNotInContainerException::class);

        // try get
        $provider->getService(SomeTestClass::class);
    }

    /**
     * Test that resolving an interface without a concrete implementation throws a specific error.
     */
    public function testServiceProviderInterfaceNoConcrete(): void
    {
        // init collection and provider
        $collection = new ServiceCollection();
        $provider = new ServiceProvider($collection);

        // add interface without concrete
        $collection->add(SomeTestClassInterface::class);

        // expect exception
        $this->expectException(ServiceProviderException::class);
        $this->expectExceptionMessage('Service "'.SomeTestClassInterface::class.'" is not instantiable');

        // try get
        $provider->getService(SomeTestClassInterface::class);
    }

    /**
     * TODO: Test cannot add same abstraction more than once. This check could also live in collection class.
     */

    /**
     * Test get a service that does not have constructor by abstraction.
     */
    public function testServiceProviderClassNoConstructor(): void
    {
        // init collection
        $collection = new ServiceCollection();

        // init provider
        $provider = new ServiceProvider($collection);

        // add service
        $collection->add(SomeTestClassInterface::class, SomeTestClassC::class);

        // get
        $object = $provider->getService(SomeTestClassInterface::class);

        // assert
        $this->assertInstanceOf(SomeTestClassC::class, $object);
    }

    /**
     * Test get a service that has a constructor by abstraction.
     */
    public function testServiceProviderAbstraction(): void
    {
        // init collection
        $collection = new ServiceCollection();

        // init provider
        $provider = new ServiceProvider($collection);

        // add service
        $collection->add(SomeTestClassInterface::class, SomeTestClass::class);

        // get
        $object = $provider->getService(SomeTestClassInterface::class);

        // assert
        $this->assertInstanceOf(SomeTestClass::class, $object);
    }

    /**
     * Test get a service that has a constructor by concrete.
     */
    public function testServiceProviderConcrete(): void
    {
        // init collection
        $collection = new ServiceCollection();

        // init provider
        $provider = new ServiceProvider($collection);

        // add service
        $collection->add(SomeTestClassInterface::class, SomeTestClassB::class);

        // get
        $object = $provider->getService(SomeTestClassB::class);

        // assert
        $this->assertInstanceOf(SomeTestClassB::class, $object);
    }

    /**
     * Test get a service two times.
     */
    public function testServiceProviderGetSame(): void
    {
        // init collection
        $collection = new ServiceCollection();

        // init provider
        $provider = new ServiceProvider($collection);

        // add service
        $collection->add(SomeTestClassInterface::class, SomeTestClassB::class);

        // get
        $objectA = $provider->getService(SomeTestClassInterface::class);
        $objectB = $provider->getService(SomeTestClassB::class);

        // assert
        $this->assertSame($objectA, $objectB);
    }

    /**
     * Test closure service resolution.
     */
    public function testServiceProviderClosure(): void
    {
        $collection = new ServiceCollection();
        $provider = new ServiceProvider($collection);

        $collection->add('closure_service', fn () => new SomeTestClass());

        $service = $provider->getService('closure_service');
        $this->assertInstanceOf(SomeTestClass::class, $service);
    }

    /**
     * Test scoped service returns same instance.
     */
    public function testServiceProviderScopedReturnsSameInstance(): void
    {
        $collection = new ServiceCollection();
        $provider = new ServiceProvider($collection);

        $collection->add(SomeTestClass::class);

        $instance1 = $provider->getService(SomeTestClass::class);
        $instance2 = $provider->getService(SomeTestClass::class);

        $this->assertSame($instance1, $instance2);
    }

    /**
     * Test ephemeral service returns new instance.
     */
    public function testServiceProviderEphemeralReturnsNewInstance(): void
    {
        $collection = new ServiceCollection();
        $provider = new ServiceProvider($collection);

        $collection->add(SomeTestClass::class, null, ServiceCollectionInterface::TYPE_EPHEMERAL);

        $instance1 = $provider->getService(SomeTestClass::class);
        $instance2 = $provider->getService(SomeTestClass::class);

        $this->assertNotSame($instance1, $instance2);
    }

    /**
     * Test primitive parameter with default value.
     */
    public function testServiceProviderPrimitiveWithDefault(): void
    {
        $collection = new ServiceCollection();
        $provider = new ServiceProvider($collection);

        $collection->add(ClassWithPrimitiveDefault::class);

        $instance = $provider->getService(ClassWithPrimitiveDefault::class);
        $this->assertEquals(123, $instance->id);
    }

    /**
     * Test nullable primitive parameter (no default) injects null.
     */
    public function testServiceProviderNullablePrimitive(): void
    {
        $collection = new ServiceCollection();
        $provider = new ServiceProvider($collection);

        $collection->add(ClassWithNullablePrimitive::class);

        $instance = $provider->getService(ClassWithNullablePrimitive::class);
        $this->assertNull($instance->id);
    }

    /**
     * Test unresolvable parameter throws exception.
     */
    public function testServiceProviderUnresolvableParam(): void
    {
        $collection = new ServiceCollection();
        $provider = new ServiceProvider($collection);

        $collection->add(ClassWithUnresolvable::class);

        $this->expectException(ServiceProviderException::class);
        $this->expectExceptionMessage('Parameter "id" in class "'.ClassWithUnresolvable::class.'" cannot be resolved');

        $provider->getService(ClassWithUnresolvable::class);
    }

    /**
     * Test dependency with default null.
     */
    public function testServiceProviderDependencyWithDefault(): void
    {
        $collection = new ServiceCollection();
        $provider = new ServiceProvider($collection);

        $collection->add(ClassWithDependencyDefault::class);

        $instance = $provider->getService(ClassWithDependencyDefault::class);
        $this->assertNull($instance->dep);
    }
}

// test interface
interface SomeTestClassInterface
{
}

// test abstraction
abstract class SomeTestClassBase implements SomeTestClassInterface
{
}

// test concrete
class SomeTestClass extends SomeTestClassBase
{
    public function __construct()
    {
    }
}

// test concrete 2
class SomeTestClassB extends SomeTestClassBase
{
    public function __construct()
    {
    }
}

// test concrete 3 no constructor
class SomeTestClassC extends SomeTestClassBase
{
}

class ClassWithPrimitiveDefault
{
    public function __construct(public int $id = 123) {}
}

class ClassWithNullablePrimitive
{
    public function __construct(public ?int $id) {}
}

class ClassWithUnresolvable
{
    public function __construct(public $id) {}
}

class ClassWithDependencyDefault
{
    public function __construct(public ?SomeTestClassInterface $dep = null) {}
}