<?php
namespace Concept\Http\Message\Request\Files;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UploadedFileInterface;
use InvalidArgumentException;

class UploadedFileFactory implements UploadedFileFactoryInterface
{
    /**
     * @var UploadedFileInterface|null
     */
    protected ?UploadedFileInterface $uploadedFilePrototype = null;

    /**
     * Constructor to inject the prototype for uploaded files.
     *
     * @param UploadedFileInterface $uploadedFilePrototype
     */
    public function __construct(UploadedFileInterface $uploadedFilePrototype)
    {
        $this->uploadedFilePrototype = $uploadedFilePrototype;
    }

    /**
     * {@inheritDoc}
     */
    public function createUploadedFile(
        StreamInterface $stream,
        ?int $size = null,
        int $error = UPLOAD_ERR_OK,
        ?string $clientFilename = null,
        ?string $clientMediaType = null
    ): UploadedFileInterface {
        $uploadedFile = clone $this->uploadedFilePrototype;

        // Verify the type of the cloned object
        if (!$uploadedFile instanceof UploadedFile) {
            throw new InvalidArgumentException('Invalid prototype provided; must be an instance of \Concept\Http\Message\Request\UploadedFile for current version');
        }

        $uploadedFile = $uploadedFile
            ->withStream($stream)
            ->withSize($size ?? $stream->getSize())
            ->withError($error)
            ->withClientFilename($clientFilename)
            ->withClientMediaType($clientMediaType);

        return $uploadedFile;
    }
}
