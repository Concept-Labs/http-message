<?php
namespace Concept\Http\Message\Request;

use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;
use Concept\Http\Message\Request\Files\UploadedFileNormalizer;

class ServerRequestFactory implements ServerRequestFactoryInterface
{
    protected ?ServerRequestInterface $serverRequestInstance = null;
    protected ?UriFactoryInterface $uriFactory = null;
    protected ?StreamFactoryInterface $streamFactory = null;
    protected ?UploadedFileNormalizer $uploadedFileNormalizer = null;

    /**
     * Dependency injection constructor.
     */
    public function __construct(
        ServerRequestInterface $serverRequestInstance, 
        UriFactoryInterface $uriFactory,
        StreamFactoryInterface $streamFactory,
        UploadedFileNormalizer $uploadedFileNormalizer
    ) {
        $this->serverRequestInstance = $serverRequestInstance;
        $this->uriFactory = $uriFactory;
        $this->streamFactory = $streamFactory;
        $this->uploadedFileNormalizer = $uploadedFileNormalizer;
    }

    /**
     * {@inheritDoc}
     */
    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        /**
         * Create a URI instance.
         */
        $uri = $this->createUri($uri);

        /**
         * Create a server request instance.
         */
        $serverRequest = $this->getServerRequestInstance()
            ->withMethod($method)
            ->withUri($uri)
            ->withQueryParams($_GET)
            ->withCookieParams($_COOKIE)
            ->withUploadedFiles($this->uploadedFileNormalizer->normalizeFiles($_FILES))
            ->withBody($this->createRequestBody())
            ->withParsedBody($_POST);

        foreach (getallheaders() as $name => $value) {
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
    protected function getServerRequestInstance(): ServerRequestInterface
    {
        return clone $this->serverRequestInstance;
    }

    /**
     * Create a request body stream.
     * 
     * @return StreamInterface
     */
    protected function createRequestBody(): StreamInterface
    {
        return $this->streamFactory->createStreamFromResource(fopen('php://input', 'r+'));
    }
}
