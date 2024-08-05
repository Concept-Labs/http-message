<?php 
namespace Concept\Http\Message\Response;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Class ResponseFactory
 * @package Concept\Http\Message\Response
 */
class ResponseFactory implements ResponseFactoryInterface
{
    /**
     * @var ResponseInterface
     */
    protected ResponseInterface $responseInstance;

    /**
     * @var StreamFactoryInterface|null
     */
    protected ?StreamFactoryInterface $streamFactory;

    /**
     * ResponseFactory constructor.
     *
     * @param ResponseInterface $responseInstance
     * @param StreamFactoryInterface|null $streamFactory
     */
    public function __construct(ResponseInterface $responseInstance, ?StreamFactoryInterface $streamFactory = null)
    {
        $this->responseInstance = $responseInstance;
        $this->streamFactory = $streamFactory;
    }

    /**
     * Get a cloned instance of the response.
     *
     * @return ResponseInterface
     */
    protected function getResponseInstance(): ResponseInterface
    {
        return clone $this->responseInstance;
    }

    /**
     * Get a cloned instance of the stream factory.
     *
     * @return StreamFactoryInterface|null
     */
    protected function getStreamFactoryInstance(): ?StreamFactoryInterface
    {
        return $this->streamFactory !== null ? clone $this->streamFactory : null;
    }

    /**
     * {@inheritDoc}
     */
    public function createResponse(int $code = StatusCodeInterface::STATUS_OK, string $reasonPhrase = ''): ResponseInterface
    {
        $response = $this->getResponseInstance()
            ->withStatus($code, $reasonPhrase);
        
        if ($this->streamFactory !== null) {
            $body = $this->getStreamFactoryInstance()->createStream();
            $response = $response->withBody($body);
        }

        return $response;
    }
}
