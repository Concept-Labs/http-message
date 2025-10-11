<?php

use Concept\Http\Message\Uri\Uri;

describe('Uri', function () {
    it('can be created', function () {
        $uri = new Uri();
        expect($uri)->toBeInstanceOf(Psr\Http\Message\UriInterface::class);
    });

    it('can set and get scheme', function () {
        $uri = new Uri();
        $newUri = $uri->withScheme('https');
        
        expect($newUri->getScheme())->toBe('https');
        expect($uri->getScheme())->toBe(''); // Original unchanged
    });

    it('can set and get host', function () {
        $uri = new Uri();
        $newUri = $uri->withHost('example.com');
        
        expect($newUri->getHost())->toBe('example.com');
    });

    it('lowercases host', function () {
        $uri = new Uri();
        $newUri = $uri->withHost('EXAMPLE.COM');
        
        expect($newUri->getHost())->toBe('example.com');
    });

    it('can set and get port', function () {
        $uri = new Uri();
        $newUri = $uri->withPort(8080);
        
        expect($newUri->getPort())->toBe(8080);
    });

    it('returns null for default port', function () {
        $uri = new Uri();
        $newUri = $uri->withScheme('http')->withPort(80);
        
        expect($newUri->getPort())->toBeNull();
    });

    it('can set and get path', function () {
        $uri = new Uri();
        $newUri = $uri->withPath('/path/to/resource');
        
        expect($newUri->getPath())->toBe('/path/to/resource');
    });

    it('can set and get query', function () {
        $uri = new Uri();
        $newUri = $uri->withQuery('foo=bar&baz=qux');
        
        expect($newUri->getQuery())->toBe('foo=bar&baz=qux');
    });

    it('can set and get fragment', function () {
        $uri = new Uri();
        $newUri = $uri->withFragment('section');
        
        expect($newUri->getFragment())->toBe('section');
    });

    it('can set user info', function () {
        $uri = new Uri();
        $newUri = $uri->withUserInfo('user', 'pass');
        
        expect($newUri->getUserInfo())->toBe('user:pass');
    });

    it('can set user info without password', function () {
        $uri = new Uri();
        $newUri = $uri->withUserInfo('user');
        
        expect($newUri->getUserInfo())->toBe('user');
    });

    it('builds authority correctly', function () {
        $uri = new Uri();
        $newUri = $uri->withScheme('http')
            ->withUserInfo('user', 'pass')
            ->withHost('example.com')
            ->withPort(8080);
        
        expect($newUri->getAuthority())->toBe('user:pass@example.com:8080');
    });

    it('builds complete URI string', function () {
        $uri = new Uri();
        $newUri = $uri->withScheme('https')
            ->withHost('example.com')
            ->withPath('/path')
            ->withQuery('key=value')
            ->withFragment('section');
        
        expect((string) $newUri)->toBe('https://example.com/path?key=value#section');
    });

    it('throws exception for invalid scheme', function () {
        $uri = new Uri();
        $uri->withScheme('invalid-scheme');
    })->throws(InvalidArgumentException::class);

    it('allows empty scheme', function () {
        $uri = new Uri();
        $newUri = $uri->withScheme('');
        
        expect($newUri->getScheme())->toBe('');
    });

    it('throws exception for invalid port', function () {
        $uri = new Uri();
        $uri->withPort(99999);
    })->throws(InvalidArgumentException::class);

    it('creates prototype instance', function () {
        $uri = new Uri();
        $uri->setUri('http://example.com/test');
        $prototype = $uri->prototype();
        
        expect($prototype)->not->toBe($uri);
        expect($prototype->getScheme())->toBe('');
    });
});
