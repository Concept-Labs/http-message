# Streams

Streams represent message bodies in PSR-7. This library provides a complete stream implementation with support for various resource types.

## Stream Basics

### Creating Streams

```php
use Concept\Http\Message\Stream\Stream;

$stream = new Stream();

// From memory
$resource = fopen('php://memory', 'r+');
fwrite($resource, 'Hello, World!');
rewind($resource);
$stream->setResource($resource);

echo $stream->getContents(); // 'Hello, World!'
```

### Stream Factory

Use `StreamFactory` to create streams:

```php
use Concept\Http\Message\Stream\StreamFactory;
use Concept\Http\Message\Stream\Stream;

$factory = new StreamFactory(new Stream());

// From string
$stream = $factory->createStream('Hello, World!');

// From file
$stream = $factory->createStreamFromFile('/path/to/file.txt', 'r');

// From resource
$resource = fopen('php://temp', 'r+');
$stream = $factory->createStreamFromResource($resource);
```

## Stream Operations

### Reading

```php
// Read entire contents
$contents = $stream->getContents();

// Read specific amount
$chunk = $stream->read(1024); // Read 1KB

// Read as string
$string = (string) $stream;

// Check if readable
if ($stream->isReadable()) {
    $data = $stream->getContents();
}
```

### Writing

```php
// Check if writable
if ($stream->isWritable()) {
    $bytesWritten = $stream->write('New content');
}
```

### Seeking

```php
// Check if seekable
if ($stream->isSeekable()) {
    // Seek to position
    $stream->seek(100);
    
    // Get current position
    $position = $stream->tell();
    
    // Rewind to start
    $stream->rewind();
}
```

### Stream Metadata

```php
// Get size
$size = $stream->getSize();

// Check if at end
$isAtEnd = $stream->eof();

// Get all metadata
$metadata = $stream->getMetadata();

// Get specific metadata
$mode = $stream->getMetadata('mode');
$uri = $stream->getMetadata('uri');
```

### Resource Management

```php
// Detach resource (stream becomes unusable)
$resource = $stream->detach();
if (is_resource($resource)) {
    // Use raw resource
    fclose($resource);
}

// Close stream
$stream->close();
```

## Stream Types

### Memory Streams

Perfect for temporary data:

```php
$stream = $factory->createStream('Temporary data');
// Or manually:
$resource = fopen('php://memory', 'r+');
$stream->setResource($resource);
```

### File Streams

For reading/writing files:

```php
$stream = $factory->createStreamFromFile('/path/to/file.txt', 'r');

// With different modes
$stream = $factory->createStreamFromFile('/path/to/file.txt', 'w'); // Write
$stream = $factory->createStreamFromFile('/path/to/file.txt', 'a'); // Append
$stream = $factory->createStreamFromFile('/path/to/file.txt', 'r+'); // Read/Write
```

### Input Stream

For reading request body:

```php
$resource = fopen('php://input', 'r');
$stream = $factory->createStreamFromResource($resource);
$requestBody = $stream->getContents();
```

### Temp Streams

For larger temporary data:

```php
$resource = fopen('php://temp', 'r+');
$stream = $factory->createStreamFromResource($resource);
```

## Error Handling

Streams throw `RuntimeException` for various errors:

```php
try {
    // Detached stream
    $stream->detach();
    $stream->getContents(); // RuntimeException
} catch (RuntimeException $e) {
    // Handle error
}

try {
    // Invalid file
    $stream = $factory->createStreamFromFile('/invalid/path'); // RuntimeException
} catch (RuntimeException $e) {
    // Handle error
}

try {
    // Write to read-only stream
    $resource = fopen('/path/to/file.txt', 'r');
    $stream->setResource($resource);
    $stream->write('data'); // RuntimeException
} catch (RuntimeException $e) {
    // Handle error
}
```

## Stream Modes

| Mode | Read | Write | Create | Truncate | Position |
|------|------|-------|--------|----------|----------|
| r    | ✓    | ✗     | ✗      | ✗        | Start    |
| r+   | ✓    | ✓     | ✗      | ✗        | Start    |
| w    | ✗    | ✓     | ✓      | ✓        | Start    |
| w+   | ✓    | ✓     | ✓      | ✓        | Start    |
| a    | ✗    | ✓     | ✓      | ✗        | End      |
| a+   | ✓    | ✓     | ✓      | ✗        | End      |
| x    | ✗    | ✓     | ✓*     | ✗        | Start    |
| x+   | ✓    | ✓     | ✓*     | ✗        | Start    |
| c    | ✗    | ✓     | ✓      | ✗        | Start    |
| c+   | ✓    | ✓     | ✓      | ✗        | Start    |

*Fails if file exists

## Best Practices

1. **Always check capabilities**:
   ```php
   if ($stream->isReadable()) {
       $data = $stream->getContents();
   }
   ```

2. **Rewind before reading**:
   ```php
   $stream->rewind();
   $contents = $stream->getContents();
   ```

3. **Use factories**: Create streams through `StreamFactory`

4. **Handle resources properly**:
   ```php
   $resource = $stream->detach();
   if (is_resource($resource)) {
       fclose($resource);
   }
   ```

5. **Choose appropriate stream type**:
   - Use `php://memory` for small data
   - Use `php://temp` for larger data
   - Use file streams for persistent data

## Common Patterns

### Reading Large Files

```php
$stream = $factory->createStreamFromFile('/large/file.txt');
while (!$stream->eof()) {
    $chunk = $stream->read(8192); // 8KB chunks
    // Process chunk
}
```

### Writing Response

```php
$data = json_encode(['status' => 'success', 'data' => $result]);
$stream = $factory->createStream($data);
$response = $response->withBody($stream);
```

### Copying Streams

```php
$source = $factory->createStreamFromFile('/source.txt');
$dest = $factory->createStreamFromFile('/dest.txt', 'w');

$source->rewind();
while (!$source->eof()) {
    $dest->write($source->read(8192));
}
```

## Next Steps

- Learn about [URIs](uris.md)
- Explore [Messages](messages.md)
- Use [Factories](factories.md)
