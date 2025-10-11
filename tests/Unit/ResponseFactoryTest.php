<?php

use Concept\Http\Message\Response\ResponseFactory;
use Concept\Http\Message\Response\Response;
use Concept\Http\Message\Stream\StreamFactory;
use Concept\Http\Message\Stream\Stream;

describe('ResponseFactory', function () {
    beforeEach(function () {
        $this->factory = new ResponseFactory(
            new Response(),
            new StreamFactory(new Stream())
        );
    });

    it('can create response', function () {
        $response = $this->factory->createResponse();
        
        expect($response)->toBeInstanceOf(Psr\Http\Message\ResponseInterface::class);
        expect($response->getStatusCode())->toBe(200);
    });

    it('can create response with custom status code', function () {
        $response = $this->factory->createResponse(404);
        
        expect($response->getStatusCode())->toBe(404);
        expect($response->getReasonPhrase())->toBe('Not Found');
    });

    it('can create response with custom reason phrase', function () {
        $response = $this->factory->createResponse(200, 'Custom');
        
        expect($response->getReasonPhrase())->toBe('Custom');
    });

    it('creates response with empty body stream', function () {
        $response = $this->factory->createResponse();
        
        expect((string) $response->getBody())->toBe('');
    });

    it('creates independent response instances', function () {
        $response1 = $this->factory->createResponse(200);
        $response2 = $this->factory->createResponse(404);
        
        expect($response1)->not->toBe($response2);
        expect($response1->getStatusCode())->toBe(200);
        expect($response2->getStatusCode())->toBe(404);
    });
});
