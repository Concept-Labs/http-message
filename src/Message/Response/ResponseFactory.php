<?php 
namespace Concept\Http\Message\Response;

use Concept\Singularity\Contract\Lifecycle\SharedInterface;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Class ResponseFactory
 * @package Concept\Http\Message\Response
 */
class ResponseFactory implements ResponseFactoryInterface, SharedInterface
{
    /**
     * @var ResponseInterface
     */
    protected ?ResponseInterface $responsePrototype = null;
    protected ?StreamFactoryInterface $streamFactry = null;

    /**
     * ResponseFactory constructor.
     *
     * @param ResponseInterface $responsePrototype
     */
    public function __construct(ResponseInterface $responsePrototype, StreamFactoryInterface $streamFactry)
    {
        $this->responsePrototype = $responsePrototype;
        $this->streamFactry = $streamFactry;
    }

    /**
     * Get a cloned instance of the response prototype.
     *
     * @return ResponseInterface
     */
    protected function getResponsePrototype(): ResponseInterface
    {
        return clone $this->responsePrototype;
    }

    /**
     * {@inheritDoc}
     */
    public function createResponse(int $code = StatusCodeInterface::STATUS_OK, string $reasonPhrase = ''): ResponseInterface
    {
        $response = $this->getResponsePrototype()
            ->withStatus($code, $reasonPhrase)
            ->withBody($this->getStreamPrototype()->createStream());

        return $response;
    }

    /**
     * @return StreamFactoryInterface
     */
    protected function getStreamPrototype(): StreamFactoryInterface
    {
        return $this->streamFactry;
    }

    
}
