<?php
namespace Concept\Http\Message\Request;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Concept\Http\Message\Message;
use Fig\Http\Message\RequestMethodInterface;

class Request extends Message implements RequestInterface
{
    /**
     * @var UriInterface|null
     */
    protected ?UriInterface $uri = null;

    /**
     * @var string
     */
    protected string $method = RequestMethodInterface::METHOD_GET;

    /**
     * @var string|null
     */
    protected ?string $requestTarget = null;

    /**
     * {@inheritDoc}
     */
    public function getRequestTarget(): string
    {
        if ($this->requestTarget !== null) {
            return $this->requestTarget;
        }

        $target = $this->uri->getPath();
        if ($target === '') {
            $target = '/';
        }

        if ($this->uri->getQuery() !== '') {
            $target .= '?' . $this->uri->getQuery();
        }

        return $target;
    }

    /**
     * {@inheritDoc}
     */
    public function withRequestTarget($requestTarget): RequestInterface
    {
        $new = clone $this;
        $new->requestTarget = $requestTarget;

        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * {@inheritDoc}
     */
    public function withMethod($method): RequestInterface
    {
        $new = clone $this;
        $new->method = $method;

        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function getUri(): UriInterface
    {
        if ($this->uri === null) {
            throw new \RuntimeException('URI is not set.');
        }
        return $this->uri;
    }

    /**
     * {@inheritDoc}
     */
    public function withUri(UriInterface $uri, $preserveHost = false): RequestInterface
    {
        $new = clone $this;
        $new->uri = $uri;

        if (!$preserveHost || !$this->hasHeader('Host')) {
            $host = $uri->getHost();
            if ($host !== '') {
                if ($uri->getPort()) {
                    $host .= ':' . $uri->getPort();
                }
                $new->headers['host'] = [$host];
            }
        }

        return $new;
    }
}
