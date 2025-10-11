<?php
namespace Concept\Http\Message;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;
use InvalidArgumentException;

class Message implements MessageInterface
{
    protected ?StreamInterface $body = null;
    protected array $headers = [];
    protected string $protocol = '1.1';


    /**
     * {@inheritDoc}
     */
    public function getProtocolVersion(): string
    {
        return $this->protocol;
    }

    /**
     * {@inheritDoc}
     */
    public function withProtocolVersion(string $version): MessageInterface
    {
        $new = clone $this;
        $new->protocol = $version;

        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * {@inheritDoc}
     */
    public function hasHeader(string $name): bool
    {
        return isset($this->headers[strtolower($name)]);
    }

    /**
     * {@inheritDoc}
     */
    public function getHeader(string $name): array
    {
        return $this->headers[strtolower($name)] ?? [];
    }

    /**
     * {@inheritDoc}
     */
    public function getHeaderLine(string $name): string
    {
        return implode(',', $this->getHeader($name));
    }

    /**
     * {@inheritDoc}
     */
    public function withHeader(string $name, $value): MessageInterface
    {
        $this->validateHeader($name, $value);

        $new = clone $this;
        $new->headers[strtolower($name)] = (array) $value;

        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function withAddedHeader(string $name, $value): MessageInterface
    {
        $this->validateHeader($name, $value);

        $new = clone $this;
        $new->headers[strtolower($name)] = array_merge($this->getHeader($name), (array) $value);

        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function withoutHeader(string $name): MessageInterface
    {
        $new = clone $this;
        unset($new->headers[strtolower($name)]);

        return $new;
    }

    /**
     * {@inheritDoc}
     */
    public function getBody(): StreamInterface
    {
        if ($this->body === null) {
            throw new \RuntimeException('Body stream is not set.');
        }
        return $this->body;
    }

    /**
     * {@inheritDoc}
     */
    public function withBody(StreamInterface $body): MessageInterface
    {
        $new = clone $this;
        $new->body = $body;

        return $new;
    }

    /**
     * Validate the header name and value.
     *
     * @param string $name
     * @param mixed $value
     * @throws InvalidArgumentException
     */
    private function validateHeader(string $name, $value): void
    {
        if (!is_string($name) || empty($name)) {
            throw new InvalidArgumentException('Header name must be a non-empty string.');
        }

        if (!is_string($value) && !is_array($value)) {
            throw new InvalidArgumentException('Header value must be a string or an array of strings.');
        }

        if (is_array($value)) {
            foreach ($value as $v) {
                if (!is_string($v)) {
                    throw new InvalidArgumentException('Header values must be strings.');
                }
            }
        }
    }
}
