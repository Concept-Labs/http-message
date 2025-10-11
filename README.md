# HTTP Message

A PSR-7 and PSR-17 compliant HTTP Message implementation for PHP 8.2+. This library provides a complete set of HTTP message abstractions following the PHP-FIG standards, designed to work seamlessly with the Singularity Container ecosystem.

[![Tests](https://img.shields.io/badge/tests-passing-brightgreen.svg)](https://github.com/Concept-Labs/http-message)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.2-blue.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

## Features

- ✅ **PSR-7 Compliance**: Full implementation of HTTP message interfaces
- ✅ **PSR-17 Compliance**: Complete set of HTTP factories
- ✅ **Immutable Messages**: All message modifications return new instances
- ✅ **Stream Support**: Robust stream handling for request/response bodies
- ✅ **URI Handling**: Complete URI manipulation and validation
- ✅ **File Uploads**: PSR-7 compliant uploaded file handling
- ✅ **Server Requests**: Full server-side request support
- ✅ **Singularity Compatible**: Designed for the Singularity Container ecosystem
- ✅ **Fully Tested**: Comprehensive test coverage with PEST

## Installation

```bash
composer require concept-labs/http-message
```

## Requirements

- PHP 8.2 or higher
- PSR HTTP Message (psr/http-message)
- PSR HTTP Factory (psr/http-factory)
- Fig HTTP Message Utilities (fig/http-message-util)

## Quick Start

### Creating a Response

```php
use Concept\Http\Message\Response\ResponseFactory;
use Concept\Http\Message\Response\Response;
use Concept\Http\Message\Stream\StreamFactory;
use Concept\Http\Message\Stream\Stream;

// Create factories
$streamFactory = new StreamFactory(new Stream());
$responseFactory = new ResponseFactory(new Response(), $streamFactory);

// Create a response
$response = $responseFactory->createResponse(200, 'OK');
$response = $response->withHeader('Content-Type', 'application/json');
```

### Creating a Request

```php
use Concept\Http\Message\Request\RequestFactory;
use Concept\Http\Message\Request\Request;
use Concept\Http\Message\Uri\UriFactory;
use Concept\Http\Message\Uri\Uri;

// Create factories
$uriFactory = new UriFactory(new Uri());
$requestFactory = new RequestFactory($uriFactory, $streamFactory, new Request());

// Create a request
$request = $requestFactory->createRequest('GET', 'https://api.example.com/users');
$request = $request->withHeader('Accept', 'application/json');
```

### Working with URIs

```php
use Concept\Http\Message\Uri\Uri;

$uri = new Uri();
$uri = $uri->withScheme('https')
    ->withHost('example.com')
    ->withPath('/api/v1/users')
    ->withQuery('page=1&limit=10')
    ->withFragment('results');

echo $uri; // https://example.com/api/v1/users?page=1&limit=10#results
```

### Working with Streams

```php
use Concept\Http\Message\Stream\Stream;
use Concept\Http\Message\Stream\StreamFactory;

$streamFactory = new StreamFactory(new Stream());

// Create from string
$stream = $streamFactory->createStream('Hello, World!');

// Create from file
$stream = $streamFactory->createStreamFromFile('/path/to/file.txt');

// Create from resource
$resource = fopen('php://memory', 'r+');
$stream = $streamFactory->createStreamFromResource($resource);
```

## Documentation

For detailed documentation, please refer to:

- [Getting Started](docs/getting-started.md)
- [HTTP Messages](docs/messages.md)
- [Streams](docs/streams.md)
- [URIs](docs/uris.md)
- [Factories](docs/factories.md)
- [Server Requests](docs/server-requests.md)
- [File Uploads](docs/file-uploads.md)
- [Singularity Integration](docs/singularity.md)

## PSR-7 Components

### Messages

- `Message` - Base HTTP message implementation
- `Request` - HTTP request implementation
- `ServerRequest` - Server-side HTTP request
- `Response` - HTTP response implementation

### Streams

- `Stream` - PSR-7 stream implementation
- `StreamFactory` - Creates stream instances

### URIs

- `Uri` - URI implementation with validation
- `UriFactory` - Creates URI instances

### Uploaded Files

- `UploadedFile` - Uploaded file representation
- `UploadedFileFactory` - Creates uploaded file instances
- `UploadedFileNormalizer` - Normalizes $_FILES array

## PSR-17 Factories

All factory implementations follow PSR-17:

- `RequestFactory` - Creates PSR-7 requests
- `ResponseFactory` - Creates PSR-7 responses
- `ServerRequestFactory` - Creates PSR-7 server requests
- `StreamFactory` - Creates PSR-7 streams
- `UploadedFileFactory` - Creates PSR-7 uploaded files
- `UriFactory` - Creates PSR-7 URIs

## Singularity Container Integration

This library is designed to work with the [Singularity Container](https://github.com/Concept-Labs/singularity) ecosystem. The `concept.json` configuration file defines service bindings for automatic dependency injection.

Key integration features:
- **Shared Services**: Factories are marked as shared (singletons)
- **Prototype Services**: Message objects are prototypes (new instance per request)
- **Automatic Wiring**: Container automatically resolves dependencies

See [Singularity Integration](docs/singularity.md) for details.

## Testing

The library includes comprehensive tests using PEST:

```bash
# Install dependencies
composer install

# Run tests
composer test

# Run tests with coverage
./vendor/bin/pest --coverage
```

## Architecture

### SOLID Principles

The library follows SOLID principles:

- **Single Responsibility**: Each class has a focused purpose
- **Open/Closed**: Extensible through inheritance and composition
- **Liskov Substitution**: All implementations properly extend base interfaces
- **Interface Segregation**: Small, focused interfaces
- **Dependency Inversion**: Depends on abstractions, not concretions

### Design Patterns

- **Factory Pattern**: PSR-17 factories for object creation
- **Prototype Pattern**: Message objects support cloning
- **Immutable Objects**: All message modifications return new instances
- **Dependency Injection**: Constructor-based dependency injection

## Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Ensure all tests pass
6. Submit a pull request

## License

This library is licensed under the MIT License. See [LICENSE](LICENSE) for details.

## Credits

Developed by Viktor Halytskyi and the Concept Labs team.

## Support

- 📧 Email: concept.galitsky@gmail.com
- 🐛 Issues: [GitHub Issues](https://github.com/Concept-Labs/http-message/issues)
- 📖 Documentation: [docs/](docs/)

## Related Projects

- [Singularity Container](https://github.com/Concept-Labs/singularity) - Dependency injection container
- [PSR-7](https://www.php-fig.org/psr/psr-7/) - HTTP message interfaces
- [PSR-17](https://www.php-fig.org/psr/psr-17/) - HTTP factories
