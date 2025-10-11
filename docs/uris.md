# URIs

The URI component provides complete URI manipulation following RFC 3986 and PSR-7 standards.

## URI Components

A URI consists of the following components:

```
https://user:pass@example.com:8080/path/to/resource?query=value#fragment
\___/   \________/ \_________/ \__/\_______________/ \__________/ \______/
  |         |           |        |         |              |          |
scheme   userinfo     host     port      path          query     fragment
```

## Creating URIs

### Manual Creation

```php
use Concept\Http\Message\Uri\Uri;

$uri = new Uri();
$uri = $uri->withScheme('https')
    ->withHost('example.com')
    ->withPort(8080)
    ->withPath('/api/users')
    ->withQuery('page=1&limit=10')
    ->withFragment('results');

echo $uri; // https://example.com:8080/api/users?page=1&limit=10#results
```

### Using URI Factory

```php
use Concept\Http\Message\Uri\UriFactory;
use Concept\Http\Message\Uri\Uri;

$factory = new UriFactory(new Uri());

$uri = $factory->createUri('https://user:pass@example.com:8080/path?query=value#fragment');

echo $uri->getScheme();    // 'https'
echo $uri->getUserInfo();  // 'user:pass'
echo $uri->getHost();      // 'example.com'
echo $uri->getPort();      // 8080
echo $uri->getPath();      // '/path'
echo $uri->getQuery();     // 'query=value'
echo $uri->getFragment();  // 'fragment'
```

## URI Components

### Scheme

```php
$uri = $uri->withScheme('https');
echo $uri->getScheme(); // 'https'

// Supported schemes: http, https, ftp, ssh, sftp, telnet, smtp, ldap, rtsp
```

### User Info

```php
// With password
$uri = $uri->withUserInfo('user', 'pass');
echo $uri->getUserInfo(); // 'user:pass'

// Without password
$uri = $uri->withUserInfo('user');
echo $uri->getUserInfo(); // 'user'
```

### Host

```php
$uri = $uri->withHost('example.com');
echo $uri->getHost(); // 'example.com'

// Host is automatically lowercased
$uri = $uri->withHost('EXAMPLE.COM');
echo $uri->getHost(); // 'example.com'
```

### Port

```php
$uri = $uri->withPort(8080);
echo $uri->getPort(); // 8080

// Default ports return null
$uri = $uri->withScheme('http')->withPort(80);
echo $uri->getPort(); // null

$uri = $uri->withScheme('https')->withPort(443);
echo $uri->getPort(); // null

// Remove port
$uri = $uri->withPort(null);
echo $uri->getPort(); // null
```

### Path

```php
$uri = $uri->withPath('/api/v1/users');
echo $uri->getPath(); // '/api/v1/users'

// Empty path
$uri = $uri->withPath('');
echo $uri->getPath(); // ''
```

### Query

```php
$uri = $uri->withQuery('page=1&limit=10');
echo $uri->getQuery(); // 'page=1&limit=10'

// Query is URL decoded when retrieved
$uri = $uri->withQuery('name=John%20Doe');
echo $uri->getQuery(); // 'name=John Doe'

// Remove query
$uri = $uri->withQuery('');
echo $uri->getQuery(); // ''
```

### Fragment

```php
$uri = $uri->withFragment('section-1');
echo $uri->getFragment(); // 'section-1'

// Fragment is URL decoded when retrieved
$uri = $uri->withFragment('section%201');
echo $uri->getFragment(); // 'section 1'

// Remove fragment
$uri = $uri->withFragment('');
echo $uri->getFragment(); // ''
```

## Authority

The authority component combines user info, host, and port:

```php
$uri = $uri->withUserInfo('user', 'pass')
    ->withHost('example.com')
    ->withPort(8080);

echo $uri->getAuthority(); // 'user:pass@example.com:8080'

// Without user info
$uri = $uri->withHost('example.com')->withPort(8080);
echo $uri->getAuthority(); // 'example.com:8080'

// With default port (null)
$uri = $uri->withScheme('http')->withHost('example.com')->withPort(80);
echo $uri->getAuthority(); // 'example.com'
```

## Default Ports

Standard ports for common schemes:

| Scheme  | Default Port |
|---------|-------------|
| http    | 80          |
| https   | 443         |
| ftp     | 21          |
| ssh     | 22          |
| sftp    | 22          |
| telnet  | 23          |
| smtp    | 25          |
| ldap    | 389         |
| rtsp    | 554         |

## Validation

The URI class validates input:

```php
// Invalid scheme
try {
    $uri->withScheme('invalid'); // InvalidArgumentException
} catch (InvalidArgumentException $e) {
    // Handle error
}

// Invalid port
try {
    $uri->withPort(99999); // InvalidArgumentException
} catch (InvalidArgumentException $e) {
    // Handle error
}

// Valid port range: 1-65535
$uri->withPort(1);     // OK
$uri->withPort(65535); // OK
$uri->withPort(0);     // InvalidArgumentException
$uri->withPort(65536); // InvalidArgumentException
```

## URI String Representation

```php
$uri = $uri->withScheme('https')
    ->withHost('example.com')
    ->withPath('/api/users')
    ->withQuery('page=1')
    ->withFragment('results');

// Explicit conversion
echo $uri->__toString();

// Implicit conversion
echo $uri;

// Both output: https://example.com/api/users?page=1#results
```

## Common Patterns

### Building API URLs

```php
$apiUri = $uri->withScheme('https')
    ->withHost('api.example.com')
    ->withPath('/v1/users')
    ->withQuery('limit=10&offset=20');
```

### Adding Query Parameters

```php
// Parse existing query
parse_str($uri->getQuery(), $params);

// Add new parameter
$params['new_param'] = 'value';

// Update URI
$uri = $uri->withQuery(http_build_query($params));
```

### Changing Protocol

```php
// HTTP to HTTPS
$secureUri = $uri->withScheme('https');

// HTTPS to HTTP
$insecureUri = $uri->withScheme('http');
```

### Removing Components

```php
// Remove user info
$uri = $uri->withUserInfo('');

// Remove port
$uri = $uri->withPort(null);

// Remove query
$uri = $uri->withQuery('');

// Remove fragment
$uri = $uri->withFragment('');
```

## Immutability

All URI modifications return a new instance:

```php
$uri1 = $factory->createUri('http://example.com');
$uri2 = $uri1->withScheme('https');

echo $uri1->getScheme(); // 'http' (unchanged)
echo $uri2->getScheme(); // 'https' (new instance)
```

## Prototype Pattern

URIs support the prototype pattern for efficient cloning:

```php
$prototype = new Uri();
$uri = $prototype->prototype(); // Creates clean clone

$uri->setUri('http://example.com/test');
$newUri = $uri->prototype(); // Clean clone

echo $uri->getPath();    // '/test'
echo $newUri->getPath(); // '' (clean state)
```

## Best Practices

1. **Use factories**: Create URIs through `UriFactory`
2. **Validate schemes**: Only use supported schemes
3. **Handle ports carefully**: Know when ports are returned as null
4. **Encode query strings**: Use `http_build_query()` for query params
5. **Be aware of immutability**: Always capture returned values

## Next Steps

- Learn about [Streams](streams.md)
- Explore [Messages](messages.md)
- Use [Factories](factories.md)
