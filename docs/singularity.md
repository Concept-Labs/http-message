# Singularity Container Integration

This library is designed to work seamlessly with the [Singularity Container](https://github.com/Concept-Labs/singularity) ecosystem, providing automatic dependency injection and service management.

## What is Singularity Container?

Singularity Container is a modern dependency injection container for PHP that provides:

- **Automatic dependency resolution**
- **Lifecycle management** (Singleton, Prototype, Shared, etc.)
- **Configuration-based bindings**
- **Type-safe dependency injection**
- **Service factories**

## Configuration

The library includes a `concept.json` file that defines service bindings for Singularity:

```json
{
    "singularity": {
        "package": {
            "concept-labs/http-message": {
                "preference": {
                    "Psr\\Http\\Message\\UriInterface": {
                        "class": "Concept\\Http\\Message\\Uri\\Uri"
                    },
                    "Psr\\Http\\Message\\UriFactoryInterface": {
                        "class": "Concept\\Http\\Message\\Uri\\UriFactory",
                        "singleton": true
                    }
                }
            }
        }
    }
}
```

## Service Lifecycles

### Singleton (Shared)

Factories are typically singletons - one instance shared across the application:

```json
{
    "Psr\\Http\\Message\\ResponseFactoryInterface": {
        "class": "Concept\\Http\\Message\\Response\\ResponseFactory",
        "singleton": true
    }
}
```

### Prototype

Message objects are prototypes - new instance created each time:

```json
{
    "Psr\\Http\\Message\\ResponseInterface": {
        "class": "Concept\\Http\\Message\\Response\\Response"
    }
}
```

## Complete Service Configuration

The `concept.json` defines all services:

```json
{
    "singularity": {
        "package": {
            "concept-labs/http-message": {
                "preference": {
                    "Psr\\Http\\Message\\UriInterface": {
                        "class": "Concept\\Http\\Message\\Uri\\Uri"
                    },
                    "Psr\\Http\\Message\\UriFactoryInterface": {
                        "class": "Concept\\Http\\Message\\Uri\\UriFactory",
                        "singleton": true
                    },
                    "Psr\\Http\\Message\\RequestInterface": {
                        "class": "Concept\\Http\\Message\\Request\\Request"
                    },
                    "Psr\\Http\\Message\\RequestFactoryInterface": {
                        "class": "Concept\\Http\\Message\\Request\\RequestFactory",
                        "singleton": true
                    },
                    "Psr\\Http\\Message\\ServerRequestInterface": {
                        "class": "Concept\\Http\\Message\\Request\\ServerRequest"
                    },
                    "Psr\\Http\\Message\\ServerRequestFactoryInterface": {
                        "class": "Concept\\Http\\Message\\Request\\ServerRequestGlobalsFactory",
                        "singleton": true
                    },
                    "Psr\\Http\\Message\\ResponseInterface": {
                        "class": "Concept\\Http\\Message\\Response\\Response"
                    },
                    "Psr\\Http\\Message\\ResponseFactoryInterface": {
                        "class": "Concept\\Http\\Message\\Response\\ResponseFactory",
                        "singleton": true
                    },
                    "Psr\\Http\\Message\\StreamInterface": {
                        "class": "Concept\\Http\\Message\\Stream\\Stream"
                    },
                    "Psr\\Http\\Message\\StreamFactoryInterface": {
                        "class": "Concept\\Http\\Message\\Stream\\StreamFactory",
                        "singleton": true
                    },
                    "Psr\\Http\\Message\\UploadedFileInterface": {
                        "class": "Concept\\Http\\Message\\Request\\Files\\UploadedFile"
                    },
                    "Psr\\Http\\Message\\UploadedFileFactoryInterface": {
                        "class": "Concept\\Http\\Message\\Request\\Files\\UploadedFileFactory",
                        "singleton": true
                    },
                    "Concept\\Http\\Message\\Request\\Files\\UploadedFileNormalizerInterface": {
                        "class": "Concept\\Http\\Message\\Request\\Files\\UploadedFileNormalizer"
                    }
                }
            }
        }
    }
}
```

## Usage with Singularity

### Basic Usage

```php
use Concept\Singularity\Container;

// Initialize container
$container = new Container();

// Automatically resolves dependencies
$responseFactory = $container->get(Psr\Http\Message\ResponseFactoryInterface::class);

// Create response
$response = $responseFactory->createResponse(200);
```

### Constructor Injection

```php
class UserController
{
    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private StreamFactoryInterface $streamFactory,
        private UserRepository $userRepository
    ) {}

    public function index(): ResponseInterface
    {
        $users = $this->userRepository->findAll();
        
        $body = $this->streamFactory->createStream(
            json_encode(['users' => $users])
        );

        return $this->responseFactory->createResponse(200)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($body);
    }
}

// Container automatically injects dependencies
$controller = $container->get(UserController::class);
$response = $controller->index();
```

### Service Resolution

```php
// Request factory (singleton)
$factory1 = $container->get(RequestFactoryInterface::class);
$factory2 = $container->get(RequestFactoryInterface::class);
$factory1 === $factory2; // true - same instance

// Request message (prototype)
$request1 = $container->get(RequestInterface::class);
$request2 = $container->get(RequestInterface::class);
$request1 === $request2; // false - different instances
```

## Lifecycle Interfaces

The library provides stub implementations for Singularity lifecycle interfaces:

### SharedInterface

Marks a service as shared (singleton):

```php
namespace Concept\Singularity\Contract\Lifecycle;

interface SharedInterface
{
}
```

Used by:
- `ResponseFactory`
- `StreamFactory`

### PrototypeInterface

Marks a service as prototype:

```php
namespace Concept\Singularity\Contract\Lifecycle;

interface PrototypeInterface
{
    public function prototype(): static;
}
```

Used by:
- `Uri`

## Integration Example

### Application Setup

```php
use Concept\Singularity\Container;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

// Initialize container
$container = new Container();

// Get server request from globals
$serverRequest = $container->get(ServerRequestInterface::class);

// Process through middleware stack
$response = $middleware->process($serverRequest);

// Emit response
http_response_code($response->getStatusCode());

foreach ($response->getHeaders() as $name => $values) {
    foreach ($values as $value) {
        header(sprintf('%s: %s', $name, $value), false);
    }
}

echo $response->getBody();
```

### Middleware Stack

```php
class Application
{
    public function __construct(
        private Container $container,
        private array $middleware = []
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Create middleware pipeline
        $pipeline = array_reduce(
            array_reverse($this->middleware),
            function ($next, $middleware) {
                return function ($request) use ($middleware, $next) {
                    return $this->container->get($middleware)->process($request, $next);
                };
            },
            function ($request) {
                // Final handler - route to controller
                return $this->route($request);
            }
        );

        return $pipeline($request);
    }

    private function route(ServerRequestInterface $request): ResponseInterface
    {
        $path = $request->getUri()->getPath();
        $method = $request->getMethod();

        // Route to controller
        $controller = $this->container->get(UserController::class);
        
        return match([$method, $path]) {
            ['GET', '/users'] => $controller->index(),
            ['POST', '/users'] => $controller->create($request),
            default => $this->notFound()
        };
    }

    private function notFound(): ResponseInterface
    {
        $responseFactory = $this->container->get(ResponseFactoryInterface::class);
        return $responseFactory->createResponse(404);
    }
}

// Usage
$app = new Application($container, [
    AuthenticationMiddleware::class,
    JsonBodyParserMiddleware::class,
    CorsMiddleware::class,
]);

$request = $container->get(ServerRequestInterface::class);
$response = $app->handle($request);
```

## Custom Service Registration

You can extend the configuration:

```php
// In your application's concept.json
{
    "singularity": {
        "package": {
            "your-vendor/your-package": {
                "preference": {
                    "Your\\Custom\\Interface": {
                        "class": "Your\\Custom\\Implementation",
                        "singleton": true
                    }
                }
            }
        }
    }
}
```

## Benefits of Singularity Integration

1. **Automatic Wiring**: No manual factory instantiation
2. **Type Safety**: Constructor injection with type hints
3. **Lifecycle Management**: Proper singleton/prototype handling
4. **Configuration-Based**: Declarative service definitions
5. **Testing**: Easy to mock dependencies

## Testing with Singularity

```php
use PHPUnit\Framework\TestCase;

class UserControllerTest extends TestCase
{
    public function testIndex()
    {
        $container = new Container();
        
        // Mock repository
        $mockRepo = $this->createMock(UserRepository::class);
        $mockRepo->method('findAll')->willReturn([
            ['id' => 1, 'name' => 'John']
        ]);
        
        // Override binding
        $container->set(UserRepository::class, $mockRepo);
        
        // Get controller with mocked dependency
        $controller = $container->get(UserController::class);
        $response = $controller->index();
        
        $this->assertEquals(200, $response->getStatusCode());
    }
}
```

## Best Practices

1. **Type hint interfaces**: Always use PSR interfaces, not implementations
2. **Constructor injection**: Inject dependencies via constructor
3. **Avoid service locator**: Don't pass container to services
4. **Use factories**: Let container manage factory singletons
5. **Configure properly**: Define lifecycles in concept.json

## Standalone Usage

The library can also be used without Singularity:

```php
// Manual instantiation
$streamPrototype = new Stream();
$streamFactory = new StreamFactory($streamPrototype);

$uriPrototype = new Uri();
$uriFactory = new UriFactory($uriPrototype);

$requestPrototype = new Request();
$requestFactory = new RequestFactory(
    $uriFactory,
    $streamFactory,
    $requestPrototype
);

// Use factories
$request = $requestFactory->createRequest('GET', 'https://example.com');
```

The stub interfaces ensure the code works with or without Singularity Container.

## Next Steps

- Explore [Getting Started](getting-started.md)
- Learn about [Factories](factories.md)
- Understand [Server Requests](server-requests.md)
