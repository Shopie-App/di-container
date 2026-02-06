<?php

declare(strict_types=1);

namespace Shopie\DiContainer\Tests;

use PHPUnit\Framework\TestCase;
use Shopie\DiContainer\Exception\ServiceInCollectionException;
use Shopie\DiContainer\ServiceCollection;

final class ServiceCollectionTest extends TestCase
{
    /**
     * Verifies that unique services are counted correctly.
     */
    public function testSetAddsUniqueServices(): void
    {
        // init collection
        $collection = new ServiceCollection();

        // add services
        $collection->add(TestClass::class);

        $collection->add(TestClassInterface::class, TestClass::class);

        // expecting collection to have only 2 items
        $this->assertEquals(2, $collection->count);
    }

    /**
     * Verifies that the container PROTECTS uniqueness by throwing.
     */
    public function testSetPreventsDuplicates(): void
    {
        $collection = new ServiceCollection();
        $id = 'service.one';
        
        // add the service
        $collection->add(TestClass::class);

        // expect exception
        $this->expectException(ServiceInCollectionException::class);

        // add it again
        $collection->add(TestClass::class);
    }

    /**
     * Test add and get by interface or concrete.
     */
    public function testServiceCollectionGetByInterface(): void
    {
        // init collection
        $collection = new ServiceCollection();

        // add
        $collection->add(TestClassInterface::class, TestClass::class);

        // get by interface
        $service = $collection->get(TestClassInterface::class);

        // assert
        $this->assertEquals(TestClass::class, $service->concrete);
    }

    /**
     * Test add and get by parent abstraction.
     */
    public function testServiceCollectionGetByAbstract(): void
    {
        // init collection
        $collection = new ServiceCollection();

        // add
        $collection->add(TestClassBase::class, TestClassB::class);

        // get by parent
        $service = $collection->get(TestClassBase::class);

        // assert
        $this->assertEquals(TestClassB::class, $service->concrete);
    }

    /**
     * Test add and get by concrete.
     */
    public function testServiceCollectionGetByConcrete(): void
    {
        // init collection
        $collection = new ServiceCollection();

        // add a service
        $collection->add(TestClassInterface::class, TestClass::class);

        // request the service
        $service = $collection->get(TestClass::class);

        // assert
        $this->assertEquals(TestClass::class, $service->concrete);
    }

    /**
     * Test and and get many with same abstraction by concrete.
     */
    public function testServiceCollectionGetManyWithSameAbstraction(): void
    {
        // init collection
        $collection = new ServiceCollection();

        // add a service
        $collection->add(TestClassInterface::class, TestClass::class);

        // expect exception
        $this->expectException(ServiceInCollectionException::class);

        // add another service
        $collection->add(TestClassInterface::class, TestClassB::class);

        // request first service
        $serviceA = $collection->get(TestClass::class);

        // request second service
        $serviceB = $collection->get(TestClassB::class);

        // assert
        $this->assertEquals(TestClass::class, $serviceA->concrete);
        $this->assertEquals(TestClassB::class, $serviceB->concrete);
    }

    /**
     * Test and and get many by concrete.
     */
    public function testServiceCollectionGetManyConcrete(): void
    {
        // init collection
        $collection = new ServiceCollection();

        // add a service
        $collection->add(TestClass::class);

        // add another service
        $collection->add(TestClassB::class);

        // request first service
        $serviceA = $collection->get(TestClass::class);

        // request second service
        $serviceB = $collection->get(TestClassB::class);

        // assert
        $this->assertEquals(TestClass::class, $serviceA->concrete);
        $this->assertEquals(TestClassB::class, $serviceB->concrete);
    }

    /**
     * Test a service is removed.
     */
    public function testCanRemoveService(): void
    {
        $collection = new ServiceCollection();
        $collection->add(TestClassB::class);

        $this->assertEquals(1, $collection->count);
        
        $collection->remove(TestClassB::class);
        
        $this->assertEquals(0, $collection->count);
        $this->assertFalse($collection->exists(TestClassB::class));
    }
}

// test interface
interface TestClassInterface
{
}

// test abstraction
abstract class TestClassBase implements TestClassInterface
{
}

// test concrete
class TestClass extends TestClassBase
{
}

// test concrete 2
class TestClassB extends TestClassBase
{
}