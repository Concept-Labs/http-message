# File Uploads

The library provides PSR-7 compliant file upload handling, including normalization of the `$_FILES` superglobal array and uploaded file management.

## Uploaded File Interface

The `UploadedFile` class implements `Psr\Http\Message\UploadedFileInterface`:

```php
use Concept\Http\Message\Request\Files\UploadedFile;

$uploadedFile = new UploadedFile();
$uploadedFile = $uploadedFile->withStream($stream)
    ->withSize($size)
    ->withError($errorCode)
    ->withClientFilename($filename)
    ->withClientMediaType($mediaType);
```

## Creating Uploaded Files

### Using Factory

```php
use Concept\Http\Message\Request\Files\UploadedFileFactory;
use Concept\Http\Message\Request\Files\UploadedFile;
use Concept\Http\Message\Stream\StreamFactory;
use Concept\Http\Message\Stream\Stream;

$streamFactory = new StreamFactory(new Stream());
$uploadedFileFactory = new UploadedFileFactory(new UploadedFile());

// Create from uploaded file
$stream = $streamFactory->createStreamFromFile($_FILES['avatar']['tmp_name']);
$uploadedFile = $uploadedFileFactory->createUploadedFile(
    $stream,
    $_FILES['avatar']['size'],
    $_FILES['avatar']['error'],
    $_FILES['avatar']['name'],
    $_FILES['avatar']['type']
);
```

## File Upload Normalizer

The normalizer converts the `$_FILES` array to PSR-7 format:

```php
use Concept\Http\Message\Request\Files\UploadedFileNormalizer;

$normalizer = new UploadedFileNormalizer(
    $uploadedFileFactory,
    $streamFactory
);

// Normalize $_FILES
$normalizedFiles = $normalizer->normalizeFiles($_FILES);
```

### Before Normalization ($_FILES)

```php
// Single file
$_FILES = [
    'avatar' => [
        'name' => 'profile.jpg',
        'type' => 'image/jpeg',
        'tmp_name' => '/tmp/phpABC123',
        'error' => 0,
        'size' => 12345
    ]
];

// Multiple files
$_FILES = [
    'documents' => [
        'name' => ['doc1.pdf', 'doc2.pdf'],
        'type' => ['application/pdf', 'application/pdf'],
        'tmp_name' => ['/tmp/phpXYZ1', '/tmp/phpXYZ2'],
        'error' => [0, 0],
        'size' => [5000, 6000]
    ]
];
```

### After Normalization (PSR-7)

```php
// Single file - UploadedFileInterface object
$normalizedFiles = [
    'avatar' => UploadedFileInterface
];

// Multiple files - array of UploadedFileInterface objects
$normalizedFiles = [
    'documents' => [
        0 => UploadedFileInterface,
        1 => UploadedFileInterface
    ]
];
```

## Accessing Uploaded Files

### File Properties

```php
// Get stream
$stream = $uploadedFile->getStream();
$contents = (string) $stream;

// Get size
$size = $uploadedFile->getSize(); // bytes

// Get error code
$error = $uploadedFile->getError();

// Get client filename (original name)
$filename = $uploadedFile->getClientFilename();

// Get client media type
$mediaType = $uploadedFile->getClientMediaType();
```

### Upload Error Codes

```php
switch ($uploadedFile->getError()) {
    case UPLOAD_ERR_OK:
        // Success
        break;
    case UPLOAD_ERR_INI_SIZE:
        // File exceeds upload_max_filesize
        break;
    case UPLOAD_ERR_FORM_SIZE:
        // File exceeds MAX_FILE_SIZE in HTML form
        break;
    case UPLOAD_ERR_PARTIAL:
        // File was only partially uploaded
        break;
    case UPLOAD_ERR_NO_FILE:
        // No file was uploaded
        break;
    case UPLOAD_ERR_NO_TMP_DIR:
        // Missing temporary folder
        break;
    case UPLOAD_ERR_CANT_WRITE:
        // Failed to write file to disk
        break;
    case UPLOAD_ERR_EXTENSION:
        // PHP extension stopped the upload
        break;
}
```

## Moving Uploaded Files

```php
$targetPath = '/var/www/uploads/avatar.jpg';

if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
    $uploadedFile->moveTo($targetPath);
}
```

### Move Validation

The library validates moves:

```php
// Invalid target path
try {
    $uploadedFile->moveTo(''); // InvalidArgumentException
} catch (InvalidArgumentException $e) {
    // Handle error
}

// Already moved
try {
    $uploadedFile->moveTo('/path/one.jpg');
    $uploadedFile->moveTo('/path/two.jpg'); // RuntimeException
} catch (RuntimeException $e) {
    // Handle error
}

// Directory not writable
try {
    $uploadedFile->moveTo('/readonly/path/file.jpg'); // RuntimeException
} catch (RuntimeException $e) {
    // Handle error
}
```

## Complete Upload Example

### HTML Form

```html
<form action="/upload" method="POST" enctype="multipart/form-data">
    <input type="file" name="avatar">
    <input type="file" name="documents[]" multiple>
    <button type="submit">Upload</button>
</form>
```

### Server-Side Handler

```php
class UploadController
{
    public function __construct(
        private UploadedFileNormalizerInterface $fileNormalizer,
        private ResponseFactoryInterface $responseFactory,
        private StreamFactoryInterface $streamFactory
    ) {}

    public function handleUpload(ServerRequestInterface $request): ResponseInterface
    {
        $uploadedFiles = $request->getUploadedFiles();
        $results = [];

        // Handle single file
        if (isset($uploadedFiles['avatar'])) {
            $avatar = $uploadedFiles['avatar'];
            
            if ($avatar->getError() === UPLOAD_ERR_OK) {
                // Validate
                if (!$this->validateImage($avatar)) {
                    return $this->errorResponse('Invalid image file');
                }
                
                // Generate unique filename
                $filename = $this->generateFilename($avatar->getClientFilename());
                $targetPath = '/var/www/uploads/avatars/' . $filename;
                
                // Move file
                $avatar->moveTo($targetPath);
                
                $results['avatar'] = $filename;
            }
        }

        // Handle multiple files
        if (isset($uploadedFiles['documents']) && is_array($uploadedFiles['documents'])) {
            $results['documents'] = [];
            
            foreach ($uploadedFiles['documents'] as $index => $document) {
                if ($document->getError() === UPLOAD_ERR_OK) {
                    // Validate
                    if (!$this->validateDocument($document)) {
                        continue;
                    }
                    
                    // Generate unique filename
                    $filename = $this->generateFilename($document->getClientFilename());
                    $targetPath = '/var/www/uploads/documents/' . $filename;
                    
                    // Move file
                    $document->moveTo($targetPath);
                    
                    $results['documents'][] = $filename;
                }
            }
        }

        // Return response
        $body = $this->streamFactory->createStream(json_encode([
            'status' => 'success',
            'uploads' => $results
        ]));

        return $this->responseFactory->createResponse(200)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($body);
    }

    private function validateImage(UploadedFileInterface $file): bool
    {
        // Check size (max 5MB)
        if ($file->getSize() > 5 * 1024 * 1024) {
            return false;
        }

        // Check mime type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file->getClientMediaType(), $allowedTypes)) {
            return false;
        }

        return true;
    }

    private function validateDocument(UploadedFileInterface $file): bool
    {
        // Check size (max 10MB)
        if ($file->getSize() > 10 * 1024 * 1024) {
            return false;
        }

        // Check mime type
        $allowedTypes = ['application/pdf', 'application/msword', 'text/plain'];
        if (!in_array($file->getClientMediaType(), $allowedTypes)) {
            return false;
        }

        return true;
    }

    private function generateFilename(string $originalName): string
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $basename = pathinfo($originalName, PATHINFO_FILENAME);
        
        // Sanitize
        $basename = preg_replace('/[^a-zA-Z0-9_-]/', '', $basename);
        
        // Add timestamp and random string
        return sprintf(
            '%s_%s_%s.%s',
            $basename,
            time(),
            bin2hex(random_bytes(4)),
            $extension
        );
    }

    private function errorResponse(string $message): ResponseInterface
    {
        $body = $this->streamFactory->createStream(json_encode([
            'status' => 'error',
            'message' => $message
        ]));

        return $this->responseFactory->createResponse(400)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($body);
    }
}
```

## Security Best Practices

### 1. Validate File Types

```php
// Don't trust client media type alone
$clientType = $file->getClientMediaType();

// Use finfo to detect actual type
$finfo = new finfo(FILEINFO_MIME_TYPE);
$actualType = $finfo->file($file->getStream()->getMetadata('uri'));

if ($actualType !== $clientType) {
    throw new RuntimeException('File type mismatch');
}
```

### 2. Validate File Size

```php
$maxSize = 5 * 1024 * 1024; // 5MB

if ($file->getSize() > $maxSize) {
    throw new RuntimeException('File too large');
}
```

### 3. Sanitize Filenames

```php
// Remove path traversal attempts
$filename = basename($file->getClientFilename());

// Remove special characters
$filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);

// Generate unique name
$filename = uniqid() . '_' . $filename;
```

### 4. Store Outside Web Root

```php
// Good - outside web root
$uploadDir = '/var/uploads/';

// Bad - inside web root
$uploadDir = '/var/www/public/uploads/'; // Accessible via web!
```

### 5. Check Upload Errors

```php
if ($file->getError() !== UPLOAD_ERR_OK) {
    $message = match ($file->getError()) {
        UPLOAD_ERR_INI_SIZE => 'File exceeds maximum size',
        UPLOAD_ERR_PARTIAL => 'File partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file uploaded',
        default => 'Upload failed'
    };
    
    throw new RuntimeException($message);
}
```

### 6. Validate Image Content

```php
if (str_starts_with($file->getClientMediaType(), 'image/')) {
    $tmpPath = $file->getStream()->getMetadata('uri');
    
    if (!getimagesize($tmpPath)) {
        throw new RuntimeException('Invalid image file');
    }
}
```

## File Upload Configuration

### PHP Configuration

```ini
; Maximum upload file size
upload_max_filesize = 10M

; Maximum POST data size
post_max_size = 10M

; Maximum number of files
max_file_uploads = 20

; Memory limit
memory_limit = 128M
```

### Web Server Configuration

#### Apache

```apache
# .htaccess
php_value upload_max_filesize 10M
php_value post_max_size 10M
```

#### Nginx

```nginx
# nginx.conf
client_max_body_size 10M;
```

## Next Steps

- Explore [Server Requests](server-requests.md)
- Learn about [Factories](factories.md)
- Integrate with [Singularity](singularity.md)
