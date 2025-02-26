<?php
namespace Concept\Http\Message\Uri;

trait UriTrait
{
    public function setUri(string $uri): static
    {
        $this->uri = $uri;
        $this->parseUri($uri);

        return $this;
    }

    /**
     * Parse the URI string into components
     * 
     * @param string $uri The URI string
     * 
     * @return static
     */
    protected function parseUri(string $uri): static
    {
        $this->components = parse_url($uri);

        return $this;
    }

    protected function withUri(string $uri): static
    {
        if (strcmp($uri, $this->__toString()) === 0) {
            return $this;
        }
        
        $clone = clone $this;
        
        $clone->setUri($uri);

        return $clone;
    }

    protected function withComponents(array $components): static
    {
        $clone = clone $this;
        
        $clone->components = $components;

        return $clone;
    }

    protected function withComponent(int $component, $value): static
    {
        $components = $this->components;
        $components[$component] = $value;

        return $this->withComponents($components);
    }

    protected function getUriString(): string
    {
        $url = [];

        $url[] = !empty($this->getScheme()) ? $this->getScheme() . '://' : '';

        if (!empty($this->getComponent(PHP_URL_USER))) {
            $url[] = $this->getComponent(PHP_URL_USER);
            if (!empty($this->getComponent(PHP_URL_PASS))) {
                $url[] = ':' . $this->getComponent(PHP_URL_PASS);
            }
            $url[] = '@';
        }

        $url[] = $this->getHost();

        if (null !== $this->getPort()) {
            $url[] = ':' . $this->getPort();
        }

        $url[] = $this->getPath();

        if (!empty($this->getQuery())) {
            $url[] = '?' . $this->getQuery();
        }

        if (!empty($this->getFragment())) {
            $url[] = '#' . $this->getFragment();
        }

        return implode('', $url);
    }

    /**
     * Get URI component
     *
     * @param integer $component The component (e.g. PHP_URL_SCHEME)
     * 
     * @return string|null The URI component
     */
    protected function getComponent(int $component): ?string
    {
        return $this->components[$component] ?? null;
    }
}
