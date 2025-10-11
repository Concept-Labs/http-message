# HTTP Messages

HTTP messages are the foundation of the PSR-7 specification. This library provides implementations for all message types.

## Message Types

### Base Message

The `Message` class provides common functionality for all HTTP messages:

```php
use Concept\Http\Message\Message;
use Concept\Http\Message\Stream\Stream;

$message = new Message();

// Protocol version
$message = $message->withProtocolVersion('2.0');
echo $message->getProtocolVersion(); // '2.0'

// Headers (case-insensitive)
$message = $message->withHeader('Content-Type', 'application/json');
$message = $message->withAddedHeader('Accept', 'application/json');
$message = $message->withAddedHeader('Accept', 'text/html');

echo $message->hasHeader('content-type'); // true
print_r($message->getHeader('Accept')); // ['application/json', 'text/html']
echo $message->getHeaderLine('Accept'); // 'application/json,text/html'

// Remove headers
$message = $message->withoutHeader('Accept');

// Body
$stream = new Stream();
$resource = fopen('php://memory', 'r+');
fwrite($resource, 'Body content');
rewind($resource);
$stream->setResource($resource);

$message = $message->withBody($stream);
echo $message->getBody(); // 'Body content'
```

### Request

The `Request` class represents an HTTP request:

```php
use Concept\Http\Message\Request\Request;
use Concept\Http\Message\Uri\Uri;

$request = new Request();

// HTTP method
$request = $request->withMethod('POST');
echo $request->getMethod(); // 'POST'

// URI
$uri = (new Uri())->withScheme('https')
    ->withHost('example.com')
    ->withPath('/api/users');

$request = $request->withUri($uri);
echo $request->getUri()->getHost(); // 'example.com'

// Request target
echo $request->getRequestTarget(); // '/api/users'

$request = $request->withRequestTarget('/custom-target');
echo $request->getRequestTarget(); // '/custom-target'

// Host header automatically set from URI
print_r($request->getHeader('Host')); // ['example.com']
```

### Response

The `Response` class represents an HTTP response:

```php
use Concept\Http\Message\Response\Response;

$response = new Response(200, 'OK');

// Status code
echo $response->getStatusCode(); // 200
echo $response->getReasonPhrase(); // 'OK'

// Change status
$response = $response->withStatus(404, 'Not Found');
echo $response->getStatusCode(); // 404
echo $response->getReasonPhrase(); // 'Not Found'

// Default reason phrases
$response = $response->withStatus(500);
echo $response->getReasonPhrase(); // 'Internal Server Error'
```

### Server Request

The `ServerRequest` class represents a server-side HTTP request with additional data:

```php
use Concept\Http\Message\Request\ServerRequest;

$serverRequest = new ServerRequest();

// Server parameters ($_SERVER)
$serverParams = $serverRequest->getServerParams();

// Cookie parameters
$serverRequest = $serverRequest->withCookieParams(['session_id' => 'abc123']);
echo $serverRequest->getCookieParams()['session_id']; // 'abc123'

// Query parameters
$serverRequest = $serverRequest->withQueryParams(['page' => '1', 'limit' => '10']);
print_r($serverRequest->getQueryParams());

// Parsed body (e.g., POST data)
$serverRequest = $serverRequest->withParsedBody(['username' => 'john', 'password' => 'secret']);
echo $serverRequest->getParsedBody()['username']; // 'john'

// Uploaded files
$serverRequest = $serverRequest->withUploadedFiles($normalizedFiles);
$files = $serverRequest->getUploadedFiles();

// Attributes (custom data)
$serverRequest = $serverRequest->withAttribute('user_id', 123);
$serverRequest = $serverRequest->withAttribute('role', 'admin');
echo $serverRequest->getAttribute('user_id'); // 123
echo $serverRequest->getAttribute('missing', 'default'); // 'default'
print_r($serverRequest->getAttributes()); // ['user_id' => 123, 'role' => 'admin']

// Remove attribute
$serverRequest = $serverRequest->withoutAttribute('role');
```

## Header Handling

Headers are case-insensitive and can have multiple values:

```php
$message = $message->withHeader('X-Custom', 'value1');
$message = $message->withAddedHeader('X-Custom', 'value2');

// All of these work
$message->hasHeader('X-Custom');
$message->hasHeader('x-custom');
$message->hasHeader('X-CUSTOM');

// Get all values
print_r($message->getHeader('X-Custom')); // ['value1', 'value2']

// Get as comma-separated string
echo $message->getHeaderLine('X-Custom'); // 'value1,value2'

// Get all headers
$headers = $message->getHeaders();
```

## Validation

The library validates input according to PSR-7:

```php
// Invalid header name
$message->withHeader('', 'value'); // throws InvalidArgumentException

// Invalid header value
$message->withHeader('X-Test', 123); // throws InvalidArgumentException

// Valid array of strings
$message->withHeader('Accept', ['text/html', 'application/json']); // OK

// Invalid array value
$message->withHeader('X-Test', ['valid', 123]); // throws InvalidArgumentException
```

## Best Practices

1. **Always use immutable messages**: Never modify a message object directly
2. **Validate input**: The library validates per PSR-7, but validate application logic too
3. **Use factories**: Create messages through factories for consistency
4. **Handle errors**: Catch and handle validation exceptions
5. **Clone properly**: When creating prototypes, ensure proper cloning

## Next Steps

- Learn about [Streams](streams.md)
- Explore [URIs](uris.md)
- Use [Factories](factories.md)
- Handle [Server Requests](server-requests.md)
