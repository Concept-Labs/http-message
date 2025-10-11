<?php

use Concept\Http\Message\Response\Response;
use Concept\Http\Message\Stream\Stream;

describe('Response', function () {
    it('can be created with status code', function () {
        $response = new Response(200);
        
        expect($response->getStatusCode())->toBe(200);
    });

    it('can set status code', function () {
        $response = new Response();
        $newResponse = $response->withStatus(404);
        
        expect($newResponse->getStatusCode())->toBe(404);
        expect($response->getStatusCode())->toBe(200); // Original unchanged
    });

    it('uses default reason phrase for known status codes', function () {
        $response = new Response();
        $newResponse = $response->withStatus(404);
        
        expect($newResponse->getReasonPhrase())->toBe('Not Found');
    });

    it('can set custom reason phrase', function () {
        $response = new Response();
        $newResponse = $response->withStatus(200, 'Custom Reason');
        
        expect($newResponse->getReasonPhrase())->toBe('Custom Reason');
    });

    it('returns empty string for unknown status codes', function () {
        $response = new Response();
        $newResponse = $response->withStatus(999);
        
        expect($newResponse->getReasonPhrase())->toBe('');
    });

    it('has common status codes mapped', function () {
        $response = new Response();
        
        $codes = [
            200 => 'OK',
            201 => 'Created',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            500 => 'Internal Server Error',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
        ];

        foreach ($codes as $code => $phrase) {
            $newResponse = $response->withStatus($code);
            expect($newResponse->getReasonPhrase())->toBe($phrase);
        }
    });
});
