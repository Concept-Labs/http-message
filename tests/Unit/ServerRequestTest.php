<?php

use Concept\Http\Message\Request\ServerRequest;

describe('ServerRequest', function () {
    it('can get server params', function () {
        $request = new ServerRequest();
        
        expect($request->getServerParams())->toBeArray();
    });

    it('can get and set cookie params', function () {
        $request = new ServerRequest();
        $newRequest = $request->withCookieParams(['session' => 'abc123']);
        
        expect($newRequest->getCookieParams())->toBe(['session' => 'abc123']);
    });

    it('can get and set query params', function () {
        $request = new ServerRequest();
        $newRequest = $request->withQueryParams(['page' => '1']);
        
        expect($newRequest->getQueryParams())->toBe(['page' => '1']);
    });

    it('can get and set uploaded files', function () {
        $request = new ServerRequest();
        $files = ['file' => 'upload'];
        $newRequest = $request->withUploadedFiles($files);
        
        expect($newRequest->getUploadedFiles())->toBe($files);
    });

    it('can get and set parsed body', function () {
        $request = new ServerRequest();
        $body = ['key' => 'value'];
        $newRequest = $request->withParsedBody($body);
        
        expect($newRequest->getParsedBody())->toBe($body);
    });

    it('can get and set attributes', function () {
        $request = new ServerRequest();
        $newRequest = $request->withAttribute('user_id', 123);
        
        expect($newRequest->getAttribute('user_id'))->toBe(123);
    });

    it('returns default value when attribute not found', function () {
        $request = new ServerRequest();
        
        expect($request->getAttribute('missing', 'default'))->toBe('default');
    });

    it('can get all attributes', function () {
        $request = new ServerRequest();
        $newRequest = $request->withAttribute('key1', 'value1')
            ->withAttribute('key2', 'value2');
        
        expect($newRequest->getAttributes())->toBe(['key1' => 'value1', 'key2' => 'value2']);
    });

    it('can remove attributes', function () {
        $request = new ServerRequest();
        $newRequest = $request->withAttribute('key', 'value')
            ->withoutAttribute('key');
        
        expect($newRequest->getAttribute('key'))->toBeNull();
    });
});
