# Basic Setup

This guide covers the installation and initial configuration of the Shopie DI Container.

## Installation

The recommended way to install the library is via [Composer](https://getcomposer.org/):

```bash
composer require shopie/di-container
```

## Core Concepts

The container is designed around a strict separation of concerns to ensure a predictable application lifecycle:

1.  **ServiceCollection**: This is your **configuration registry**. You use it to define *what* services exist, their implementations, and their lifecycles.
2.  **ServiceProvider**: This is your **resolution engine**. You use it to *retrieve* instantiated services.

Once the `ServiceProvider` is created from a `ServiceCollection`, the service definitions are effectively locked for that provider instance.

## Initialization

To start using the container, you must instantiate the collection, register your bindings, and then create the provider.

```php
use Shopie\DiContainer\ServiceCollection;
use Shopie\DiContainer\ServiceProvider;

// 1. Initialize the Collection
$collection = new ServiceCollection();

// 2. Register Services
// (See "Service Registration" for more details on binding interfaces and closures)
$collection->add(DatabaseConnection::class);
$collection->add(UserRepository::class);

// 3. Build the Provider
// The provider requires the collection to resolve dependencies
$provider = new ServiceProvider($collection);

// 4. Resolve Dependencies
$userRepo = $provider->getService(UserRepository::class);
```

## Next Steps

*   Learn about Service Lifecycles (Scoped vs. Ephemeral).
*   Understand Auto-wiring for constructor injection.
*   See how to handle Persistent Environments (Swoole, RoadRunner).