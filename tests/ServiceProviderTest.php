<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Shopie\DiContainer\Exception\ServiceNotInContainerException;
use Shopie\DiContainer\ServiceCollection;
use Shopie\DiContainer\ServiceProvider;

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
     * TODO: Test cannot add same abstraction more than once. This check could also leave in collection class.
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
        $this->assertInstanceOf(SomeTestClassB::class, $objectA);
        $this->assertInstanceOf(SomeTestClassB::class, $objectB);
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