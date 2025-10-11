<?php
namespace Concept\Http\Message\Uri;

use Concept\Singularity\Contract\Lifecycle\PrototypeInterface;
use Psr\Http\Message\UriInterface;

class Uri implements UriInterface, PrototypeInterface
{
    use UriTrait;
    
    /**
     * Schemes with default ports
     * 
     * @var array<string, int>
     */
    const SCHEMES = [
        'http' => 80,
        'https' => 443,
        'ftp' => 21,
        'ssh' => 22,
        'sftp' => 22,
        'telnet' => 23,
        'smtp' => 25,
        'ldap' => 389,
        'rtsp' => 554,
    ];
    
    /**
     * The URI string
     * 
     * @var string
     */
    protected string $uri = '';

    /**
     * The URI components
     * 
     * @var array<int, string>
     */
    protected array $components = [];

    public function __clone()
    {
        $this->uri = '';
        $this->components = [];
    }

    /**
     * Create a new prototype instance
     * 
     * @return static
     */
    public function prototype(): static
    {
        return clone $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getScheme(): string
    {
        return $this->getComponent(PHP_URL_SCHEME) ?? '';
    }

    /**
     * {@inheritDoc}
     */
    public function getAuthority(): string
    {
        $userInfo = $this->getUserInfo();
        $host = $this->getHost();
        $port = $this->getPort();

        if (empty($userInfo) && empty($host)) {
            return '';
        }
        
        $authority = '';

        if (!empty($userInfo)) {
            $authority .= $userInfo . '@';
        }

        $authority .= $host;

        if (!empty($port)) {
            $authority .= ':' . $port;
        }
        
        return $authority;
    }

    /**
     * {@inheritDoc}
     */
    public function getUserInfo(): string
    {
        $user = $this->getComponent(PHP_URL_USER);
        $password = $this->getComponent(PHP_URL_PASS);

        if (empty($user)) {
            return '';
        }

        return $password !== null 
            ? sprintf('%s:%s', $user, $password) 
            : $user;
    }

    /**
     * {@inheritDoc}
     */
    public function getHost(): string
    {
        return $this->getComponent(PHP_URL_HOST) ?? '';
    }

    
    /**
     * {@inheritDoc}
     */
    public function getPort(): ?int
    {
        $scheme = $this->getScheme();
        $port = $this->getComponent(PHP_URL_PORT);

        if ($port === null) {
            return null;
        }

        $port = (int) $port;
        $defaultPort = $this->getDefaultPort($scheme);

        return $port === $defaultPort ? null : $port;
    }

    /**
     * Get a default scheme port
     *
     * @param string $scheme The scheme string
     * 
     * @return int|null The default port or null if not found
     */
    public function getDefaultPort(string $scheme): ?int
    {
        return static::SCHEMES[$scheme] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function getPath(): string
    {
        return $this->getComponent(PHP_URL_PATH) ?? '';
    }

    /**
     * {@inheritDoc}
     */
    public function getQuery(): string
    {
        $query = $this->getComponent(PHP_URL_QUERY);

        return !empty($query) ? rawurldecode($query) : '';
    }

    /**
     * {@inheritDoc}
     */
    public function getFragment(): string
    {
        $fragment = $this->getComponent(PHP_URL_FRAGMENT);
    
        return !empty($fragment) ? rawurldecode($fragment) : '';
    }
    
    /**
     * {@inheritDoc}
     */
    public function withScheme($scheme): UriInterface
    {
        $scheme = strtolower($scheme);
        
        if ($scheme !== '' && !array_key_exists($scheme, static::SCHEMES)) {
            throw new \InvalidArgumentException('Invalid or unsupported scheme');
        }
    
        return $this->withComponent(PHP_URL_SCHEME, $scheme);
    }

    /**
     * {@inheritDoc}
     */
    public function withUserInfo($user, $password = null): UriInterface
    {
        return $this->withComponent(PHP_URL_USER, $user)
            ->withComponent(PHP_URL_PASS, $password);
    }

    /**
     * {@inheritDoc}
     */
    public function withHost($host): UriInterface
    {
        return $this->withComponent(PHP_URL_HOST, strtolower($host));
    }

    /**
     * {@inheritDoc}
     */
    public function withPort($port): UriInterface
    {
        if ($port !== null && ($port < 1 || $port > 65535)) {
            throw new \InvalidArgumentException('Invalid port number');
        }

        return $this->withComponent(PHP_URL_PORT, $port);
    }

    /**
     * {@inheritDoc}
     */
    public function withPath($path): UriInterface
    {
        if (!is_string($path)) {
            throw new \InvalidArgumentException('Path must be a string');
        }
        
        return $this->withComponent(PHP_URL_PATH, $path);
    }

    /**
     * {@inheritDoc}
     */
    public function withQuery($query): UriInterface
    {
        return $this->withComponent(PHP_URL_QUERY, $query);
    }

    /**
     * {@inheritDoc}
     */
    public function withFragment($fragment): UriInterface
    {
        return $this->withComponent(PHP_URL_FRAGMENT, $fragment);
    }

    /**
     * {@inheritDoc}
     */
    public function __toString(): string
    {
        return $this->getUriString();
    }
}
