<?php
namespace Concept\Http\Message\Stream;

use Concept\Singularity\Contract\Lifecycle\SharedInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;


class StreamFactory implements StreamFactoryInterface, SharedInterface
{
    /**
     * @var StreamInterface|null
     */
    protected ?StreamInterface $streamInstance = null;

    /**
     * StreamFactory constructor.
     *
     * @param StreamInterface $streamInstance
     */
    public function __construct(StreamInterface $streamInstance)
    {
        $this->streamInstance = $streamInstance;
    }

    /**
     * {@inheritDoc}
     */
    public function createStream(string $content = ''): StreamInterface
    {
        $resource = fopen('php://temp', 'r+');
        fwrite($resource, $content);
        rewind($resource);

        return $this->createStreamFromResource($resource);
    }

    /**
     * {@inheritDoc}
     */
    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        $resource = fopen($filename, $mode);
        if (!$resource) {
            throw new \RuntimeException("Unable to open file: $filename");
        }

        return $this->createStreamFromResource($resource);
    }

    /**
     * {@inheritDoc}
     */
    public function createStreamFromResource($resource): StreamInterface
    {
        $stream = $this->getStreamInstance();
        $stream->setResource($resource);
        return $stream;
    }

    /**
     * Get the injected stream instance.
     * 
     * @return StreamInterface
     */
    protected function getStreamInstance(): StreamInterface
    {
        return clone $this->streamInstance;
    }
}
