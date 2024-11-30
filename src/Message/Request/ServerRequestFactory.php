<?php
namespace Concept\Http\Message\Request;

use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Concept\Http\Message\Request\Files\UploadedFileNormalizer;
use Concept\Http\Message\Request\Files\UploadedFileNormalizerInterface;

class ServerRequestFactory implements ServerRequestFactoryInterface
{
    protected ?ServerRequestInterface $serverRequestPrototype = null;
    protected ?UriFactoryInterface $uriFactory = null;
    protected ?StreamFactoryInterface $streamFactory = null;
    protected ?UploadedFileNormalizerInterface $uploadedFileNormalizer = null;

    /**
     * Dependency injection constructor.
     */
    public function __construct(
        ServerRequestInterface $serverRequestPrototype, 
        UriFactoryInterface $uriFactory,
        StreamFactoryInterface $streamFactory,
        UploadedFileNormalizerInterface $uploadedFileNormalizer
    ) {
        $this->serverRequestPrototype = $serverRequestPrototype;
        $this->uriFactory = $uriFactory;
        $this->streamFactory = $streamFactory;
        $this->uploadedFileNormalizer = $uploadedFileNormalizer;
    }

    /**
     * {@inheritDoc}
     */
    public function createServerRequest(
        string $method,
        $uri,
        array $serverParams = [], 
        ?array $headers = [], // Додано заголовки як параметр
        ?array $queryParams = [], 
        ?array $cookieParams = [], 
        ?array $uploadedFiles = [], 
        ?array $parsedBody = []
    ): ServerRequestInterface {
        $uri = $this->createUri($uri);

        $serverRequest = $this->getServerRequestPrototype()
            ->withMethod($method)
            ->withUri($uri)
            ->withQueryParams($queryParams)
            ->withCookieParams($cookieParams)
            ->withUploadedFiles($this->uploadedFileNormalizer->normalizeFiles($uploadedFiles))
            ->withBody($this->createRequestBody())
            ->withParsedBody($parsedBody);

        foreach ($headers as $name => $value) {
            $serverRequest = $serverRequest->withAddedHeader($name, $value);
        }

        /**
         * Add server attributes to the server request.
         */
        foreach (ServerRequestAttributesInterface::SERVER_ATTRIBUTES_TO_ADD as $key => $value) {
            $serverRequest = $serverRequest->withAttribute($key, $value);
        }

        return $serverRequest;
    }

    /**
     * Create a URI instance.
     * 
     * @param string $uri
     * 
     * @return UriInterface
     */
    protected function createUri($uri): UriInterface
    {
        return $this->uriFactory->createUri($uri);
    }

    /**
     * Get a cloned instance of the server request.
     * 
     * @return ServerRequestInterface
     */
    protected function getServerRequestPrototype(): ServerRequestInterface
    {
        return clone $this->serverRequestPrototype;
    }

    /**
     * Create a request body stream.
     * 
     * @return StreamInterface
     */
    protected function createRequestBody(): StreamInterface
    {
        return $this
            ->getStreamFactory()
                ->createStreamFromResource(
                    $this->getStreamResource()
                );
    }

    /**
     * Get the Stream factory.
     * 
     * @return StreamFactoryInterface
     */
    protected function getStreamFactory(): StreamFactoryInterface
    {
        return $this->streamFactory;
    }

    /**
     * Get the stream resource.
     * Default to php://input
     * @todo Implement a better way to get the stream resource.
     * 
     * @return resource
     */
    protected function getStreamResource()
    {
        return fopen('php://input', 'r+');
    }
}
