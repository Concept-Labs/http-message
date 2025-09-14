<?php
namespace Concept\Http\Message\Stream;

use Psr\Http\Message\StreamInterface;

class Stream implements StreamInterface
{
    /**
     * @var resource $resource
     */
    protected $resource = null;

    /**
     * @var array|null
     */
    protected ?array $metadata = null;

    /**
     * @var int|null
     */
    protected ?int $size = null;

    /**
     * Встановлює ресурс потоку.
     * 
     * @param resource $resource
     * @return StreamInterface
     */
    public function setResource($resource): StreamInterface
    {
        $this->resource = $resource;
        $this->metadata = stream_get_meta_data($this->resource);
        $this->size = null;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        if (!$this->isReadable()) {
            return '';
        }

        try {
            $this->rewind();
            return $this->getContents();
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        if (is_resource($this->resource)) {
            fclose($this->resource);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function detach()
    {
        $resource = $this->resource;
        $this->resource = null;
        $this->size = null;
        $this->metadata = null;

        return $resource;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        if ($this->size !== null) {
            return $this->size;
        }

        if (!is_resource($this->resource)) {
            return null;
        }

        $stats = fstat($this->resource);
        if ($stats === false) {
            return null;
        }

        if ($this->isSeekable()) {
            $this->size = $stats['size'];
        }

        return $this->size;
    }

    /**
     * {@inheritdoc}
     */
    public function tell()
    {
        if (!is_resource($this->resource)) {
            throw new \RuntimeException('Stream is detached');
        }

        $result = ftell($this->resource);
        if ($result === false) {
            throw new \RuntimeException('Unable to determine stream position');
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function eof()
    {
        return !$this->resource || feof($this->resource);
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable()
    {
        return $this->metadata['seekable'];
    }

    /**
     * {@inheritdoc}
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        if (!is_resource($this->resource)) {
            throw new \RuntimeException('Stream is detached');
        }

        if (!$this->isSeekable()) {
            throw new \RuntimeException('Stream is not seekable');
        }

        if (fseek($this->resource, $offset, $whence) === -1) {
            throw new \RuntimeException('Unable to seek to stream position ' . $offset . ' with whence ' . $whence);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->seek(0);
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable()
    {
        return preg_match('/[waxc]/i', $this->getMetadata('mode'));
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable(): bool
    {
        return preg_match('/[r+]/i', $this->getMetadata('mode'));
    }

    /**
     * {@inheritdoc}
     */
    public function write($string)
    {
        if (!is_resource($this->resource)) {
            throw new \RuntimeException('Stream is detached');
        }

        if (!$this->isWritable()) {
            throw new \RuntimeException('Cannot write to a non-writable stream');
        }

        $result = fwrite($this->resource, $string);

        if ($result === false) {
            throw new \RuntimeException('Unable to write to stream');
        }

        $this->size = null;
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function read($length)
    {
        if (!is_resource($this->resource)) {
            throw new \RuntimeException('Stream is detached');
        }

        if (!$this->isReadable()) {
            throw new \RuntimeException('Cannot read from non-readable stream');
        }

        $result = fread($this->resource, $length);
        if ($result === false) {
            throw new \RuntimeException('Unable to read from stream');
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents()
    {
        if (!is_resource($this->resource)) {
            throw new \RuntimeException('Stream is detached');
        }

        $contents = stream_get_contents($this->resource);
        if ($contents === false) {
            throw new \RuntimeException('Unable to read stream contents');
        }

        return $contents;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($key = null)
    {
        if ($key === null) {
            return $this->metadata;
        }

        return $this->metadata[$key] ?? null;
    }

    /**
     * Get the resource.
     * 
     * @return resource|null
     */
    protected function getResource()
    {
        return $this->resource;
    }
}
