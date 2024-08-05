<?php
namespace Concept\Http\Message\Response;

use Psr\Http\Message\ResponseInterface;
use Concept\Http\Message\Message;
use Concept\Http\Message\StatusReasonPhraseInterface;
use Fig\Http\Message\StatusCodeInterface;

/**
 * Class Response
 * @package Concept\Http\Message\Response
 */
class Response extends Message implements ResponseInterface, StatusCodeInterface
{
    /**
     * @var int
     */
    protected int $statusCode;

    /**
     * @var string
     */
    protected string $reasonPhrase;

    /**
     * Response constructor.
     *
     * @param int $statusCode
     * @param string $reasonPhrase
     */
    public function __construct(int $statusCode = StatusCodeInterface::STATUS_OK, string $reasonPhrase = '')
    {
        $this->statusCode = $statusCode;
        $this->reasonPhrase = $reasonPhrase;
    }

    /**
     * {@inheritDoc}
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * {@inheritDoc}
     */
    public function withStatus($code, $reasonPhrase = ''): ResponseInterface
    {
        $new = clone $this;
        $new->statusCode = $code;
        $new->reasonPhrase = $reasonPhrase ?: $this->getDefaultReasonPhrase($code);

        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    /**
     * Get the reason phrase based on the status code.
     * 
     * @param int $statusCode
     * 
     * @return string
     */
    protected function getDefaultReasonPhrase(int $statusCode): string
    {
        return StatusReasonPhraseInterface::class[$statusCode] ?? '';
    }
}
