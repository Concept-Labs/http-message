<?php
namespace Concept\Http\Message\Request\Files;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;
use InvalidArgumentException;

class UploadedFile implements UploadedFileInterface
{
    protected ?StreamInterface $stream = null;
    protected bool $moved = false;
    protected ?int $size = null;
    protected ?int $error = null;
    protected ?string $clientFilename = null;
    protected ?string $clientMediaType = null;

    /**
     * {@inheritDoc}
     */
    public function getStream(): StreamInterface
    {
        if ($this->stream === null) {
            throw new RuntimeException('No stream available');
        }

        if ($this->moved) {
            throw new RuntimeException('The file has already been moved');
        }

        return $this->stream;
    }

    /**
     * {@inheritDoc}
     */
    public function moveTo($targetPath): void
    {
        if ($this->moved) {
            throw new RuntimeException('The file has already been moved');
        }

        if (!is_string($targetPath) || empty($targetPath)) {
            throw new InvalidArgumentException('Invalid target path provided');
        }

        if (!is_writable(dirname($targetPath))) {
            throw new RuntimeException('The target path is not writable');
        }

        $this->stream->rewind();
        $targetStream = fopen($targetPath, 'w');
        stream_copy_to_stream($this->stream->detach(), $targetStream);
        fclose($targetStream);

        $this->moved = true;
    }

    /**
     * {@inheritDoc}
     */
    public function getSize(): ?int
    {
        return $this->size;
    }

    /**
     * {@inheritDoc}
     */
    public function getError(): int
    {
        return $this->error;
    }

    /**
     * {@inheritDoc}
     */
    public function getClientFilename(): ?string
    {
        return $this->clientFilename;
    }

    /**
     * {@inheritDoc}
     */
    public function getClientMediaType(): ?string
    {
        return $this->clientMediaType;
    }

    /**
     * Create a new instance with the specified stream
     *
     * @param StreamInterface $stream
     * @return static
     */
    public function withStream(StreamInterface $stream): static
    {
        $new = clone $this;
        $new->stream = $stream;
        return $new;
    }

    /**
     * Create a new instance with the specified size
     *
     * @param int $size
     * @return static
     */
    public function withSize(int $size): static
    {
        $new = clone $this;
        $new->size = $size;
        return $new;
    }

    /**
     * Create a new instance with the specified error code
     *
     * @param int $error
     * @return static
     */
    public function withError(int $error): static
    {
        $new = clone $this;
        $new->error = $error;
        return $new;
    }

    /**
     * Create a new instance with the specified client filename
     *
     * @param string $clientFilename
     * @return static
     */
    public function withClientFilename(?string $clientFilename): static
    {
        $new = clone $this;
        $new->clientFilename = $clientFilename;
        return $new;
    }

    /**
     * Create a new instance with the specified client media type
     *
     * @param string $clientMediaType
     * @return static
     */
    public function withClientMediaType(?string $clientMediaType): static
    {
        $new = clone $this;
        $new->clientMediaType = $clientMediaType;
        return $new;
    }
}
