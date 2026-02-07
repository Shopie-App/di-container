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

### Resetting Services (Persistent Environments)

In persistent environments like Swoole, RoadRunner, or FrankenPHP, services are often reused across requests. To prevent data leakage (e.g., user context, database connections), you can implement the ResettableInterface.

1. Implement ResettableInterface in your service:

```php
use Shopie\DiContainer\Contracts\ResettableInterface;

class UserService implements ResettableInterface
{
    public function reset(): void
    {
        // Reset state for the next request
        $this->currentUser = null;
    }
}
```

2. Call resetAll() on the collection at the end of the request lifecycle:

```php
// After the request is handled
$collection->resetAll();
```

### Manual Injection (Testing)

You can manually inject an instantiated object into the container using setObject. This is useful for testing (injecting mocks) or when bridging with other containers.

Note: The service must be registered first.
```php
// Register the service first
$collection->add(MyService::class);

// Inject the instance
$mock = new MockService();
$collection->setObject(MyService::class, $mock);


### Removing Services
You can remove a service definition and its instance from the collection at runtime.

```php
$collection->remove(MyService::class);
```