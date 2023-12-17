<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Shopie\DiContainer\ServiceCollection;
use Shopie\DiContainer\ServiceContainer;
use Shopie\DiContainer\ServiceProvider;

final class ServiceComplexTest extends TestCase
{
    public function testService(): void
    {
        // init service collection
        $collection = new ServiceCollection();

        // init container
        $service = new ServiceContainer($collection);

        // init provider
        $provider = new ServiceProvider($collection);

        // add test concrete service
        $service->addScoped(TestController::class);

        // add test abstract services
        $service->addScoped(TestParameterAInterface::class, TestParameterA::class);
        $service->addScoped(TestParameterBInterface::class, TestParameterB::class);
        $service->addScoped(TestParameterBA::class);

        // request the service
        $testController = $provider->getService(TestController::class);

        // do asserts
        $this->assertEquals('Hello Service!', $testController->doSomething());
        $this->assertEquals('Hello aClass!', $testController->aClass->doSomething());
        $this->assertEquals('Hello bClass!', $testController->bClass->doSomething());
        $this->assertEquals(20, $testController->bClass->param->getInt());
    }
}

// requested service
class TestController
{
    public function __construct(public TestParameterAInterface $aClass, public TestParameterBInterface $bClass)
    {
    }

    public function doSomething(): string
    {
        return 'Hello Service!';
    }
}

// requested class 1st parameter contract
interface TestParameterAInterface
{
}

// requested class 2nd parameter contract
interface TestParameterBInterface
{
}

// requested class 1st parameter
class TestParameterA implements TestParameterAInterface
{
    public function __construct()
    {
    }

    public function doSomething(): string
    {
        return 'Hello aClass!';
    }
}

// requested class 2nd parameter
class TestParameterB implements TestParameterBInterface
{
    public function __construct(public TestParameterBA $param)
    {
    }

    public function doSomething(): string
    {
        return 'Hello bClass!';
    }
}

// requested class 2nd parameter 1st parameter
class TestParameterBA
{
    public function __construct(private int $testInt = 0)
    {
    }

    public function getInt(): int
    {
        $this->testInt = 20;
        return $this->testInt;
    }
}