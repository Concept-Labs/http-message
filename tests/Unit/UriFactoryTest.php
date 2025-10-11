<?php

use Concept\Http\Message\Uri\UriFactory;
use Concept\Http\Message\Uri\Uri;

describe('UriFactory', function () {
    beforeEach(function () {
        $this->factory = new UriFactory(new Uri());
    });

    it('can create URI from string', function () {
        $uri = $this->factory->createUri('https://user:pass@example.com:8080/path?query=value#fragment');
        
        expect($uri->getScheme())->toBe('https');
        expect($uri->getUserInfo())->toBe('user:pass');
        expect($uri->getHost())->toBe('example.com');
        expect($uri->getPort())->toBe(8080);
        expect($uri->getPath())->toBe('/path');
        expect($uri->getQuery())->toBe('query=value');
        expect($uri->getFragment())->toBe('fragment');
    });

    it('can create empty URI', function () {
        $uri = $this->factory->createUri('');
        
        expect($uri->getScheme())->toBe('');
        expect($uri->getHost())->toBe('');
        expect($uri->getPath())->toBe('');
    });

    it('creates independent URI instances', function () {
        $uri1 = $this->factory->createUri('http://example.com');
        $uri2 = $this->factory->createUri('https://other.com');
        
        expect($uri1)->not->toBe($uri2);
        expect($uri1->getHost())->toBe('example.com');
        expect($uri2->getHost())->toBe('other.com');
    });
});
