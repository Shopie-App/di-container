<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Shopie\DiContainer\Exception\NoConcreteTypeException;
use Shopie\DiContainer\ServiceCollection;

final class ServiceCollectionTest extends TestCase
{
    /**
     * Test no concrete type exception thrown.
     */
    public function testServiceCollectionNoConcreteException(): void
    {
        // init collection
        $collection = new ServiceCollection();

        // register
        $this->expectException(NoConcreteTypeException::class);

        // add abstraction
        $collection->add(TestClassInterface::class);
    }

    /**
     * Test add same objects either as concrete or coupled with abstraction.
     */
    public function testServiceCollectionAddRandom(): void
    {
        // init collection
        $collection = new ServiceCollection();

        // add services
        $collection->add(TestClass::class);

        $collection->add(TestClassInterface::class, TestClass::class);

        $collection->add(TestClass::class);

        $collection->add(TestClassInterface::class, TestClassB::class);

        $collection->add(TestClass::class);

        $collection->add(TestClassB::class);

        $collection->add(TestClassInterface::class, TestClassB::class);

        // expecting collection to have only 2 items
        $this->assertEquals(2, $collection->size());
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
        $this->assertEquals(TestClass::class, $service->concreteClassName);
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
        $this->assertEquals(TestClassB::class, $service->concreteClassName);
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
        $this->assertEquals(TestClass::class, $service->concreteClassName);
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

        // add another service
        $collection->add(TestClassInterface::class, TestClassB::class);

        // request first service
        $serviceA = $collection->get(TestClass::class);

        // request second service
        $serviceB = $collection->get(TestClassB::class);

        // assert
        $this->assertEquals(TestClass::class, $serviceA->concreteClassName);
        $this->assertEquals(TestClassB::class, $serviceB->concreteClassName);
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
        $this->assertEquals(TestClass::class, $serviceA->concreteClassName);
        $this->assertEquals(TestClassB::class, $serviceB->concreteClassName);
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