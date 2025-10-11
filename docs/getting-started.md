# Getting Started

## Installation

Install the package via Composer:

```bash
composer require concept-labs/http-message
```

## Basic Usage

### 1. Create a Simple Response

```php
<?php
use Concept\Http\Message\Response\ResponseFactory;
use Concept\Http\Message\Response\Response;
use Concept\Http\Message\Stream\StreamFactory;
use Concept\Http\Message\Stream\Stream;

// Initialize factories
$streamFactory = new StreamFactory(new Stream());
$responseFactory = new ResponseFactory(new Response(), $streamFactory);

// Create response
$response = $responseFactory->createResponse(200, 'OK');
$response = $response->withHeader('Content-Type', 'application/json')
    ->withHeader('Cache-Control', 'no-cache');

// Add body
$body = $streamFactory->createStream(json_encode(['status' => 'success']));
$response = $response->withBody($body);
```

### 2. Create a Request

```php
<?php
use Concept\Http\Message\Request\RequestFactory;
use Concept\Http\Message\Request\Request;
use Concept\Http\Message\Uri\UriFactory;
use Concept\Http\Message\Uri\Uri;

// Initialize factories
$uriFactory = new UriFactory(new Uri());
$streamFactory = new StreamFactory(new Stream());
$requestFactory = new RequestFactory($uriFactory, $streamFactory, new Request());

// Create request
$request = $requestFactory->createRequest('POST', 'https://api.example.com/users');
$request = $request->withHeader('Content-Type', 'application/json')
    ->withHeader('Authorization', 'Bearer token123');

// Add body
$data = json_encode(['name' => 'John Doe', 'email' => 'john@example.com']);
$body = $streamFactory->createStream($data);
$request = $request->withBody($body);
```

### 3. Handle Server Requests

```php
<?php
use Concept\Http\Message\Request\ServerRequestGlobalsFactory;
use Concept\Http\Message\Request\ServerRequest;
use Concept\Http\Message\Uri\UriFactory;
use Concept\Http\Message\Uri\Uri;
use Concept\Http\Message\Stream\StreamFactory;
use Concept\Http\Message\Stream\Stream;
use Concept\Http\Message\Request\Files\UploadedFileNormalizer;
use Concept\Http\Message\Request\Files\UploadedFileFactory;
use Concept\Http\Message\Request\Files\UploadedFile;

// Initialize dependencies
$uriFactory = new UriFactory(new Uri());
$streamFactory = new StreamFactory(new Stream());
$uploadedFileFactory = new UploadedFileFactory(new UploadedFile());
$uploadedFileNormalizer = new UploadedFileNormalizer($uploadedFileFactory, $streamFactory);

// Create server request factory
$serverRequestFactory = new ServerRequestGlobalsFactory(
    new ServerRequest(),
    $uriFactory,
    $streamFactory,
    $uploadedFileNormalizer
);

// Create from globals
$serverRequest = $serverRequestFactory->createServerRequest(
    $_SERVER['REQUEST_METHOD'],
    $_SERVER['REQUEST_URI'],
    $_SERVER
);

// Access request data
$queryParams = $serverRequest->getQueryParams();
$cookies = $serverRequest->getCookieParams();
$uploadedFiles = $serverRequest->getUploadedFiles();
$parsedBody = $serverRequest->getParsedBody();
```

## Immutability

All HTTP message objects are immutable. When you call a `with*()` method, it returns a new instance:

```php
$response1 = $responseFactory->createResponse(200);
$response2 = $response1->withStatus(404);

// $response1 still has status 200
// $response2 has status 404
```

## Next Steps

- Learn about [HTTP Messages](messages.md)
- Explore [Streams](streams.md)
- Understand [URIs](uris.md)
- Work with [Factories](factories.md)
- Handle [Server Requests](server-requests.md)
- Manage [File Uploads](file-uploads.md)
- Integrate with [Singularity](singularity.md)
