# Server Requests

Server requests represent incoming HTTP requests on the server side, with additional context about the server environment, cookies, query parameters, and uploaded files.

## Server Request vs Request

| Request | Server Request |
|---------|---------------|
| Client-side or general purpose | Server-side only |
| Basic HTTP method, URI, headers, body | Includes server params, cookies, query params, uploaded files, parsed body, attributes |
| Created by client | Created from incoming HTTP request |

## Creating Server Requests

### From Factory

```php
use Concept\Http\Message\Request\ServerRequestFactory;
use Concept\Http\Message\Request\ServerRequest;

$serverRequest = $serverRequestFactory->createServerRequest(
    'POST',
    '/api/users',
    $_SERVER,
    $headers,
    $queryParams,
    $cookieParams,
    $uploadedFiles,
    $parsedBody
);
```

### From Globals

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

## Server Parameters

Access the `$_SERVER` superglobal:

```php
$serverParams = $serverRequest->getServerParams();

// Common server parameters
$method = $serverParams['REQUEST_METHOD'];
$uri = $serverParams['REQUEST_URI'];
$protocol = $serverParams['SERVER_PROTOCOL'];
$host = $serverParams['HTTP_HOST'];
$userAgent = $serverParams['HTTP_USER_AGENT'] ?? '';
$remoteAddr = $serverParams['REMOTE_ADDR'] ?? '';
```

## Query Parameters

Access and modify query parameters (from `$_GET`):

```php
// Get all query params
$queryParams = $serverRequest->getQueryParams();
// ['page' => '1', 'limit' => '10']

// Set query params
$serverRequest = $serverRequest->withQueryParams([
    'page' => '2',
    'limit' => '20',
    'sort' => 'name'
]);

// Access individual params
$page = $serverRequest->getQueryParams()['page'] ?? 1;
```

## Cookie Parameters

Access and modify cookie parameters (from `$_COOKIE`):

```php
// Get all cookies
$cookies = $serverRequest->getCookieParams();
// ['session_id' => 'abc123', 'user_pref' => 'dark']

// Set cookies
$serverRequest = $serverRequest->withCookieParams([
    'session_id' => 'xyz789',
    'user_pref' => 'light'
]);

// Access individual cookie
$sessionId = $serverRequest->getCookieParams()['session_id'] ?? null;
```

## Parsed Body

Access and modify the parsed request body (from `$_POST` or custom parser):

```php
// Get parsed body
$body = $serverRequest->getParsedBody();

// Array for form data
if (is_array($body)) {
    $username = $body['username'] ?? '';
    $email = $body['email'] ?? '';
}

// Object for JSON
if (is_object($body)) {
    $username = $body->username ?? '';
}

// Set parsed body
$serverRequest = $serverRequest->withParsedBody([
    'username' => 'john',
    'email' => 'john@example.com'
]);

// Can also be an object
$data = new stdClass();
$data->username = 'john';
$serverRequest = $serverRequest->withParsedBody($data);

// Or null
$serverRequest = $serverRequest->withParsedBody(null);
```

## Uploaded Files

Handle file uploads (from `$_FILES`):

```php
// Get uploaded files
$uploadedFiles = $serverRequest->getUploadedFiles();

// Single file upload
if (isset($uploadedFiles['avatar'])) {
    $avatar = $uploadedFiles['avatar'];
    
    if ($avatar->getError() === UPLOAD_ERR_OK) {
        $avatar->moveTo('/path/to/uploads/' . $avatar->getClientFilename());
    }
}

// Multiple file upload
if (isset($uploadedFiles['documents'])) {
    foreach ($uploadedFiles['documents'] as $document) {
        if ($document->getError() === UPLOAD_ERR_OK) {
            $document->moveTo('/path/to/uploads/' . $document->getClientFilename());
        }
    }
}

// Set uploaded files
$serverRequest = $serverRequest->withUploadedFiles($normalizedFiles);
```

## Request Attributes

Store derived request data and application-specific values:

```php
// Set attributes (typically by middleware)
$serverRequest = $serverRequest->withAttribute('user_id', 123);
$serverRequest = $serverRequest->withAttribute('role', 'admin');
$serverRequest = $serverRequest->withAttribute('authenticated', true);

// Get attribute with default
$userId = $serverRequest->getAttribute('user_id');
$role = $serverRequest->getAttribute('role', 'guest');

// Get all attributes
$attributes = $serverRequest->getAttributes();
// ['user_id' => 123, 'role' => 'admin', 'authenticated' => true]

// Remove attribute
$serverRequest = $serverRequest->withoutAttribute('role');
```

## Common Patterns

### Authentication Middleware

```php
class AuthenticationMiddleware
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $token = $request->getHeader('Authorization')[0] ?? '';
        
        if ($user = $this->validateToken($token)) {
            // Add user to request attributes
            $request = $request->withAttribute('user', $user);
            $request = $request->withAttribute('authenticated', true);
        }
        
        return $handler->handle($request);
    }
}
```

### JSON Body Parser Middleware

```php
class JsonBodyParserMiddleware
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $contentType = $request->getHeader('Content-Type')[0] ?? '';
        
        if (str_contains($contentType, 'application/json')) {
            $body = (string) $request->getBody();
            $data = json_decode($body, true);
            
            if (json_last_error() === JSON_ERROR_NONE) {
                $request = $request->withParsedBody($data);
            }
        }
        
        return $handler->handle($request);
    }
}
```

### Route Parameters Middleware

```php
class RouteMiddleware
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Extract route parameters
        $params = $this->matchRoute($request->getUri()->getPath());
        
        // Add to request attributes
        foreach ($params as $key => $value) {
            $request = $request->withAttribute($key, $value);
        }
        
        return $handler->handle($request);
    }
}

// Usage in controller
$userId = $request->getAttribute('id');
```

### CORS Middleware

```php
class CorsMiddleware
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $origin = $request->getHeader('Origin')[0] ?? '';
        
        $response = $handler->handle($request);
        
        if ($this->isAllowedOrigin($origin)) {
            $response = $response->withHeader('Access-Control-Allow-Origin', $origin)
                ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE')
                ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        }
        
        return $response;
    }
}
```

## Complete Request Handling Example

```php
// Create server request from globals
$serverRequest = $serverRequestGlobalsFactory->createServerRequest(
    $_SERVER['REQUEST_METHOD'],
    $_SERVER['REQUEST_URI'],
    $_SERVER
);

// Process through middleware stack
$serverRequest = $authMiddleware->process($serverRequest);
$serverRequest = $jsonBodyParser->process($serverRequest);
$serverRequest = $routeMiddleware->process($serverRequest);

// In controller
class UserController
{
    public function create(ServerRequestInterface $request): ResponseInterface
    {
        // Check authentication
        if (!$request->getAttribute('authenticated')) {
            return $this->responseFactory->createResponse(401);
        }
        
        // Get parsed body
        $data = $request->getParsedBody();
        $name = $data['name'] ?? '';
        $email = $data['email'] ?? '';
        
        // Get user from auth
        $currentUser = $request->getAttribute('user');
        
        // Create user
        $user = $this->userRepository->create($name, $email);
        
        // Build response
        $body = $this->streamFactory->createStream(
            json_encode(['user' => $user])
        );
        
        return $this->responseFactory->createResponse(201)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($body);
    }
}
```

## Server Request Attributes

Common attribute names:

| Attribute | Purpose | Set By |
|-----------|---------|--------|
| `user` | Authenticated user object | Auth middleware |
| `user_id` | User ID | Auth middleware |
| `authenticated` | Authentication status | Auth middleware |
| `route` | Matched route | Router |
| Route parameters (e.g., `id`, `slug`) | URL parameters | Router |
| `locale` | User locale | Localization middleware |
| `ip_address` | Real IP address | Proxy middleware |

## Best Practices

1. **Use attributes for derived data**: Don't modify parsed body, use attributes
2. **Validate uploaded files**: Always check error codes before moving files
3. **Parse JSON in middleware**: Parse request bodies before controllers
4. **Type-check parsed body**: It can be array, object, or null
5. **Don't trust user input**: Always validate and sanitize

## Security Considerations

```php
// Validate file uploads
if ($file->getError() !== UPLOAD_ERR_OK) {
    throw new RuntimeException('Upload failed');
}

// Check file size
if ($file->getSize() > 5 * 1024 * 1024) { // 5MB
    throw new RuntimeException('File too large');
}

// Validate mime type
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
if (!in_array($file->getClientMediaType(), $allowedTypes)) {
    throw new RuntimeException('Invalid file type');
}

// Sanitize filename
$filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientFilename());

// Validate parsed body
$data = $request->getParsedBody();
if (!is_array($data)) {
    throw new InvalidArgumentException('Invalid request body');
}

// Sanitize input
$email = filter_var($data['email'] ?? '', FILTER_SANITIZE_EMAIL);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    throw new InvalidArgumentException('Invalid email');
}
```

## Next Steps

- Learn about [File Uploads](file-uploads.md)
- Explore [Factories](factories.md)
- Integrate with [Singularity](singularity.md)
