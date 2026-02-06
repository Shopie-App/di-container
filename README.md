# DI Container

Simple IoC container for PHP.

- **Lazy loading**: Services are only instantiated when requested.
- **Auto-wiring**: Automatically resolves constructor dependencies.
- **Separation of concerns**: Distinct `ServiceCollection` (configuration) and `ServiceProvider` (resolution).
- **Lifecycle management**: Supports Scoped (singleton per provider) and Ephemeral (factory) lifecycles.

## Installation

```bash
composer require shopie/di-container
```

## Usage

### Basic Setup

The container is split into two parts:
1. `ServiceCollection`: Where you register your services.
2. `ServiceProvider`: Where you retrieve your services.

```php
use Shopie\DiContainer\ServiceCollection;
use Shopie\DiContainer\ServiceProvider;

// 1. Create the collection
$collection = new ServiceCollection();

// 2. Register services
$collection->add(MyService::class);

// 3. Create the provider
$provider = new ServiceProvider($collection);

// 4. Resolve a service
$service = $provider->getService(MyService::class);
```

### Binding Interfaces to Implementations

You can bind an interface or abstract class to a specific concrete implementation.

```php
use Shopie\DiContainer\ServiceCollection;

$collection = new ServiceCollection();

// When 'LoggerInterface' is requested, provide 'FileLogger'
$collection->add(LoggerInterface::class, FileLogger::class);
```

### Service Lifecycles

There are two types of service lifecycles:

*   **Scoped (Default)**: The instance is created once and reused for subsequent requests within the same `ServiceProvider` instance.
*   **Ephemeral**: A new instance is created every time it is requested.

```php
use Shopie\DiContainer\Contracts\ServiceCollectionInterface;

// Scoped (Default)
$collection->add(DatabaseConnection::class);

// Ephemeral
$collection->add(
    RandomNumberGenerator::class, 
    null, 
    ServiceCollectionInterface::TYPE_EPHEMERAL
);
```

### Using Closures (Factories)

You can use a closure to manually construct a service. This is useful for configuration or complex initialization.

```php
$collection->add(ApiClient::class, function (ServiceProvider $provider) {
    $apiKey = getenv('API_KEY');
    return new ApiClient($apiKey);
});
```

### Auto-wiring Dependencies

The container automatically resolves dependencies defined in the constructor.

```php
class UserController 
{
    public function __construct(private UserRepository $repo) {}
}

// Register both
$collection->add(UserRepository::class);
$collection->add(UserController::class);

// UserController will be instantiated with UserRepository injected automatically
$controller = $provider->getService(UserController::class);
```