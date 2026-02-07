# Shopie DI Container

A simple, powerful Inversion of Control (IoC) container for PHP.

## Features

*   **Lazy loading**: Services are only instantiated when they are actually requested.
*   **Auto-wiring**: Automatically resolves constructor dependencies using PHP Reflection.
*   **Separation of concerns**: Distinct `ServiceCollection` (configuration) and `ServiceProvider` (resolution) to ensure a predictable lifecycle.
*   **Lifecycle management**: Supports **Scoped** (singleton per provider) and **Ephemeral** (factory) lifecycles.
*   **Persistent Environment Support**: Built-in mechanisms to reset services between requests for environments like Swoole, RoadRunner, or FrankenPHP.

## Quick Start

Install via Composer:

```bash
composer require shopie/di-container
```

Register and resolve a service:

```php
use Shopie\DiContainer\ServiceCollection;
use Shopie\DiContainer\ServiceProvider;

// 1. Configure
$collection = new ServiceCollection();
$collection->add(Logger::class);

// 2. Build
$provider = new ServiceProvider($collection);

// 3. Resolve
$logger = $provider->getService(Logger::class);
```

## Documentation Guide

*   **Basic Setup**: Detailed installation instructions and explanation of the Collection vs Provider architecture.
*   **Service Lifecycles**: Learn when to use Scoped vs Ephemeral services.
*   **Auto-wiring**: How to let the container handle your dependencies automatically.
*   **Advanced Usage**: Handling persistent environments and manual injection for testing.