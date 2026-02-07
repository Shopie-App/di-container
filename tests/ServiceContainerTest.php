<?php

declare(strict_types=1);

namespace Shopie\DiContainer\Tests;

use PHPUnit\Framework\TestCase;
use Shopie\DiContainer\Contracts\ServiceCollectionInterface;
use Shopie\DiContainer\ServiceContainer;

final class ServiceContainerTest extends TestCase
{
    public function testResetAllDelegatesToCollection(): void
    {
        $collection = $this->createMock(ServiceCollectionInterface::class);

        $collection->expects($this->once())
            ->method('resetAll');

        $container = new ServiceContainer($collection);
        $container->resetAll();
    }

    public function testAddScopedDelegatesToCollection(): void
    {
        $collection = $this->createMock(ServiceCollectionInterface::class);

        $collection->expects($this->once())
            ->method('add')
            ->with('TestService', null, ServiceCollectionInterface::TYPE_SCOPED);

        $container = new ServiceContainer($collection);
        $container->addScoped('TestService');
    }

    public function testAddEphemeralDelegatesToCollection(): void
    {
        $collection = $this->createMock(ServiceCollectionInterface::class);

        $collection->expects($this->once())
            ->method('add')
            ->with('TestService', null, ServiceCollectionInterface::TYPE_EPHEMERAL);

        $container = new ServiceContainer($collection);
        $container->addEphemeral('TestService');
    }
}