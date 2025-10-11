# Factories

PSR-17 HTTP Factories provide a standardized way to create PSR-7 HTTP message objects. This library includes all required factory implementations.

## Factory Overview

| Factory | Creates | Interface |
|---------|---------|-----------|
| RequestFactory | HTTP Requests | `Psr\Http\Message\RequestFactoryInterface` |
| ResponseFactory | HTTP Responses | `Psr\Http\Message\ResponseFactoryInterface` |
| ServerRequestFactory | Server Requests | `Psr\Http\Message\ServerRequestFactoryInterface` |
| StreamFactory | Message Streams | `Psr\Http\Message\StreamFactoryInterface` |
| UploadedFileFactory | Uploaded Files | `Psr\Http\Message\UploadedFileFactoryInterface` |
| UriFactory | URIs | `Psr\Http\Message\UriFactoryInterface` |

## Request Factory

Creates standard HTTP requests:

```php
use Concept\Http\Message\Request\RequestFactory;
use Concept\Http\Message\Request\Request;
use Concept\Http\Message\Uri\UriFactory;
use Concept\Http\Message\Uri\Uri;
use Concept\Http\Message\Stream\StreamFactory;
use Concept\Http\Message\Stream\Stream;

// Initialize dependencies
$uriFactory = new UriFactory(new Uri());
$streamFactory = new StreamFactory(new Stream());
$requestFactory = new RequestFactory(
    $uriFactory,
    $streamFactory,
    new Request()
);

// Create request
$request = $requestFactory->createRequest('GET', 'https://api.example.com/users');
```

## Response Factory

Creates HTTP responses:

```php
use Concept\Http\Message\Response\ResponseFactory;
use Concept\Http\Message\Response\Response;

// Initialize factory
$responseFactory = new ResponseFactory(
    new Response(),
    $streamFactory
);

// Create response with default status
$response = $responseFactory->createResponse();
echo $response->getStatusCode(); // 200

// Create with custom status
$response = $responseFactory->createResponse(404, 'Not Found');
echo $response->getStatusCode(); // 404
echo $response->getReasonPhrase(); // 'Not Found'

// Create with default reason phrase
$response = $responseFactory->createResponse(500);
echo $response->getReasonPhrase(); // 'Internal Server Error'
```

## Server Request Factory

Creates server-side HTTP requests:

```php
use Concept\Http\Message\Request\ServerRequestFactory;
use Concept\Http\Message\Request\ServerRequest;
use Concept\Http\Message\Request\Files\UploadedFileNormalizer;
use Concept\Http\Message\Request\Files\UploadedFileFactory;
use Concept\Http\Message\Request\Files\UploadedFile;

// Initialize dependencies
$uploadedFileFactory = new UploadedFileFactory(new UploadedFile());
$uploadedFileNormalizer = new UploadedFileNormalizer(
    $uploadedFileFactory,
    $streamFactory
);

$serverRequestFactory = new ServerRequestFactory(
    new ServerRequest(),
    $uriFactory,
    $streamFactory,
    $uploadedFileNormalizer
);

// Create server request
$serverRequest = $serverRequestFactory->createServerRequest(
    'POST',
    'https://example.com/api/users',
    $_SERVER
);
```

### Server Request Globals Factory

Automatically loads from PHP globals:

```php
use Concept\Http\Message\Request\ServerRequestGlobalsFactory;

$globalsFactory = new ServerRequestGlobalsFactory(
    new ServerRequest(),
    $uriFactory,
    $streamFactory,
    $uploadedFileNormalizer
);

// Automatically uses $_GET, $_POST, $_COOKIE, $_FILES
$serverRequest = $globalsFactory->createServerRequest(
    $_SERVER['REQUEST_METHOD'],
    $_SERVER['REQUEST_URI'],
    $_SERVER
);
```

## Stream Factory

Creates message body streams:

```php
use Concept\Http\Message\Stream\StreamFactory;
use Concept\Http\Message\Stream\Stream;

$streamFactory = new StreamFactory(new Stream());

// From string
$stream = $streamFactory->createStream('{"status": "success"}');

// From file
$stream = $streamFactory->createStreamFromFile('/path/to/file.txt');

// From file with mode
$stream = $streamFactory->createStreamFromFile('/path/to/file.txt', 'r');

// From resource
$resource = fopen('php://memory', 'r+');
$stream = $streamFactory->createStreamFromResource($resource);
```

## Uploaded File Factory

Creates uploaded file objects:

```php
use Concept\Http\Message\Request\Files\UploadedFileFactory;
use Concept\Http\Message\Request\Files\UploadedFile;

$uploadedFileFactory = new UploadedFileFactory(new UploadedFile());

// Create uploaded file
$stream = $streamFactory->createStreamFromFile('/tmp/upload.txt');
$uploadedFile = $uploadedFileFactory->createUploadedFile(
    $stream,
    12345,              // size in bytes
    UPLOAD_ERR_OK,      // error code
    'document.txt',     // client filename
    'text/plain'        // client media type
);
```

## URI Factory

Creates URI objects:

```php
use Concept\Http\Message\Uri\UriFactory;
use Concept\Http\Message\Uri\Uri;

$uriFactory = new UriFactory(new Uri());

// Create from string
$uri = $uriFactory->createUri('https://example.com/path?query=value#fragment');

// Create empty
$uri = $uriFactory->createUri('');

// All components are parsed
echo $uri->getScheme();   // 'https'
echo $uri->getHost();     // 'example.com'
echo $uri->getPath();     // '/path'
echo $uri->getQuery();    // 'query=value'
echo $uri->getFragment(); // 'fragment'
```

## Dependency Injection

Factories use constructor injection for dependencies:

```php
class MyApplication
{
    public function __construct(
        private RequestFactoryInterface $requestFactory,
        private ResponseFactoryInterface $responseFactory,
        private StreamFactoryInterface $streamFactory
    ) {}

    public function handleRequest(): ResponseInterface
    {
        $request = $this->requestFactory->createRequest('GET', '/api/users');
        
        // Process request...
        
        $body = $this->streamFactory->createStream(
            json_encode(['users' => $users])
        );
        
        return $this->responseFactory->createResponse(200)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($body);
    }
}
```

## Factory Patterns

### Complete Factory Setup

```php
// Core dependencies
$streamPrototype = new Stream();
$uriPrototype = new Uri();

// Factories
$streamFactory = new StreamFactory($streamPrototype);
$uriFactory = new UriFactory($uriPrototype);

// Message factories
$requestFactory = new RequestFactory(
    $uriFactory,
    $streamFactory,
    new Request()
);

$responseFactory = new ResponseFactory(
    new Response(),
    $streamFactory
);

// Upload handling
$uploadedFileFactory = new UploadedFileFactory(new UploadedFile());
$uploadedFileNormalizer = new UploadedFileNormalizer(
    $uploadedFileFactory,
    $streamFactory
);

// Server request
$serverRequestFactory = new ServerRequestFactory(
    new ServerRequest(),
    $uriFactory,
    $streamFactory,
    $uploadedFileNormalizer
);

$serverRequestGlobalsFactory = new ServerRequestGlobalsFactory(
    new ServerRequest(),
    $uriFactory,
    $streamFactory,
    $uploadedFileNormalizer
);
```

### Service Container Integration

```php
// With Singularity Container (or any PSR-11 container)
$container->set(StreamFactoryInterface::class, function() {
    return new StreamFactory(new Stream());
});

$container->set(UriFactoryInterface::class, function() {
    return new UriFactory(new Uri());
});

$container->set(RequestFactoryInterface::class, function($c) {
    return new RequestFactory(
        $c->get(UriFactoryInterface::class),
        $c->get(StreamFactoryInterface::class),
        new Request()
    );
});

// Use factories
$requestFactory = $container->get(RequestFactoryInterface::class);
$request = $requestFactory->createRequest('GET', '/api/users');
```

## Prototype Pattern

Factories use prototypes to create new instances efficiently:

```php
// Response factory clones prototype for each response
$prototype = new Response();
$factory = new ResponseFactory($prototype, $streamFactory);

$response1 = $factory->createResponse(200);
$response2 = $factory->createResponse(404);

// Both are independent instances
$response1 !== $response2; // true
```

## Best Practices

1. **Use dependency injection**: Inject factories, not concrete classes
2. **Share factory instances**: Factories can be singletons/shared
3. **Type hint interfaces**: Use PSR-17 interfaces, not implementations
4. **Configure once**: Set up factories at application bootstrap
5. **Validate early**: Factories validate input, but check application logic too

## Factory Configuration

### Shared vs Prototype

In Singularity Container:

```json
{
    "Psr\\Http\\Message\\ResponseFactoryInterface": {
        "class": "Concept\\Http\\Message\\Response\\ResponseFactory",
        "singleton": true
    },
    "Psr\\Http\\Message\\ResponseInterface": {
        "class": "Concept\\Http\\Message\\Response\\Response"
    }
}
```

- Factories are typically **shared** (singleton)
- Message objects are **prototypes** (new instance each time)

## Common Use Cases

### API Response

```php
$data = ['users' => $users, 'total' => count($users)];
$body = $streamFactory->createStream(json_encode($data));

$response = $responseFactory->createResponse(200)
    ->withHeader('Content-Type', 'application/json')
    ->withBody($body);
```

### File Download

```php
$stream = $streamFactory->createStreamFromFile('/path/to/file.pdf');

$response = $responseFactory->createResponse(200)
    ->withHeader('Content-Type', 'application/pdf')
    ->withHeader('Content-Disposition', 'attachment; filename="document.pdf"')
    ->withBody($stream);
```

### HTTP Client

```php
$body = $streamFactory->createStream(json_encode(['name' => 'John']));

$request = $requestFactory->createRequest('POST', 'https://api.example.com/users')
    ->withHeader('Content-Type', 'application/json')
    ->withHeader('Authorization', 'Bearer token123')
    ->withBody($body);
```

## Next Steps

- Explore [Server Requests](server-requests.md)
- Learn about [File Uploads](file-uploads.md)
- Integrate with [Singularity](singularity.md)
