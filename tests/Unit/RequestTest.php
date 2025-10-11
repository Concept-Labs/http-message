<?php

use Concept\Http\Message\Request\Request;
use Concept\Http\Message\Uri\Uri;

describe('Request', function () {
    it('can get and set method', function () {
        $request = new Request();
        $newRequest = $request->withMethod('POST');
        
        expect($newRequest->getMethod())->toBe('POST');
        expect($request->getMethod())->toBe('GET'); // Original unchanged
    });

    it('can get and set URI', function () {
        $request = new Request();
        $uri = (new Uri())->withScheme('https')->withHost('example.com');
        $newRequest = $request->withUri($uri);
        
        expect($newRequest->getUri()->getHost())->toBe('example.com');
    });

    it('sets Host header from URI', function () {
        $request = new Request();
        $uri = (new Uri())->withScheme('https')->withHost('example.com');
        $newRequest = $request->withUri($uri);
        
        expect($newRequest->getHeader('host'))->toBe(['example.com']);
    });

    it('preserves Host header when flag is set', function () {
        $request = new Request();
        $request = $request->withHeader('Host', 'original.com');
        
        $uri = (new Uri())->withScheme('https')->withHost('example.com');
        $newRequest = $request->withUri($uri, true);
        
        expect($newRequest->getHeader('host'))->toBe(['original.com']);
    });

    it('can get request target from URI', function () {
        $request = new Request();
        $uri = (new Uri())->withScheme('http')
            ->withHost('example.com')
            ->withPath('/path')
            ->withQuery('key=value');
        $newRequest = $request->withUri($uri);
        
        expect($newRequest->getRequestTarget())->toBe('/path?key=value');
    });

    it('returns / as default request target', function () {
        $request = new Request();
        $uri = (new Uri())->withScheme('http')->withHost('example.com');
        $newRequest = $request->withUri($uri);
        
        expect($newRequest->getRequestTarget())->toBe('/');
    });

    it('can set custom request target', function () {
        $request = new Request();
        $newRequest = $request->withRequestTarget('/custom/target');
        
        expect($newRequest->getRequestTarget())->toBe('/custom/target');
    });

    it('throws exception when URI is not set', function () {
        $request = new Request();
        $request->getUri();
    })->throws(RuntimeException::class);
});
