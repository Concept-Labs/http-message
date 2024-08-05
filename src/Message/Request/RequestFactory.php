<?php
namespace Concept\Http\Message\Request;

use InvalidArgumentException;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

/**
 * Class RequestFactory
 * @package Concept\Http\Message\Request
 */
class RequestFactory implements RequestFactoryInterface
{
    /**
     * @var RequestInterface|null
     */
    protected ?RequestInterface $requestInstance = null;

    /**
     * @var UriFactoryInterface|null
     */
    protected ?UriFactoryInterface $uriFactory = null;

    /**
     * @var StreamFactoryInterface|null
     */
    protected ?StreamFactoryInterface $streamFactory = null;

    /**
     * RequestFactory constructor.
     * 
     * @param UriFactoryInterface $uriFactory
     * @param StreamFactoryInterface $streamFactory
     * @param RequestInterface $requestInstance
     */
    public function __construct(
        UriFactoryInterface $uriFactory,
        StreamFactoryInterface $streamFactory,
        RequestInterface $requestInstance
    ) {
        $this->uriFactory = $uriFactory;
        $this->streamFactory = $streamFactory;
        $this->requestInstance = $requestInstance;
    }

    /**
     * {@inheritDoc}
     */
    public function createRequest(string $method, $uri): RequestInterface
    {
        $uri = is_string($uri) ? $this->getUriFactoryInstance()->createUri($uri) : $uri;

        if (!$uri instanceof UriInterface) {
            throw new InvalidArgumentException('Invalid URI provided');
        }

        return $this->getRequestInstance()
            ->withMethod($method)
            ->withRequestTarget('')
            ->withUri($uri)
            ->withBody($this->createRequestBody());
    }

    /**
     * Get the UriFactory instance
     * 
     * @return UriFactoryInterface
     */
    protected function getUriFactoryInstance(): UriFactoryInterface
    {
        return clone $this->uriFactory;
    }

    /**
     * Get the Request instance
     * 
     * @return RequestInterface
     */
    protected function getRequestInstance(): RequestInterface
    {
        return clone $this->requestInstance;
    }

    /**
     * Get the StreamFactory instance
     * 
     * @return StreamFactoryInterface
     */
    protected function getStreamFactoryInstance(): StreamFactoryInterface
    {
        return clone $this->streamFactory;
    }

    /**
     * Create request body stream
     * 
     * @return StreamInterface
     */
    protected function createRequestBody(): StreamInterface
    {
        return $this->getStreamFactoryInstance()
            ->createStreamFromResource(
                fopen('php://input', 'r+')
            );
    }
}
