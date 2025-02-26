<?php
namespace Concept\Http\Message\Uri;

use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

class UriFactory implements UriFactoryInterface
{
    /**
     * @var UriInterface The Uri instance
     */
    protected ?UriInterface $uriPrototype = null;

    /**
     * The constructor
     *
     * @param UriInterface $uriPrototype The uri instance
     */
    public function __construct(UriInterface $uriPrototype)
    {
        $this->uriPrototype = $uriPrototype;
    }

    /**
     * {@inheritDoc}
     */
    public function createUri(string $uri = ''): UriInterface
    {
        return $this->getUriPrototype()
            ->withScheme($this->getComponent($uri, PHP_URL_SCHEME) ?? '')
            ->withUserInfo($this->getComponent($uri, PHP_URL_USER) ?? '', $this->getComponent($uri, PHP_URL_PASS) ?? '')
            ->withHost($this->getComponent($uri, PHP_URL_HOST) ?? '')
            ->withPort($this->getComponent($uri, PHP_URL_PORT) !== null ? (int)$this->getComponent($uri, PHP_URL_PORT) : null)
            ->withPath($this->getComponent($uri, PHP_URL_PATH) ?? '')
            ->withQuery($this->getComponent($uri, PHP_URL_QUERY) ?? '')
            ->withFragment($this->getComponent($uri, PHP_URL_FRAGMENT) ?? '');
    }

    /**
     * Get the injected uri instance
     * 
     * @return UriInterface
     */
    protected function getUriPrototype(): UriInterface
    {
        return clone $this->uriPrototype;
    }

    /**
     * Get the uri component
     * 
     * @param string $uri The uri string
     * @param int $component The component @see PHP_URL_*
     * 
     * @return string|null
     */
    protected function getComponent(string $uri, int $component): ?string
    {
        return parse_url($uri, $component);
    }
}
