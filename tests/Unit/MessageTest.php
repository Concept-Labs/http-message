<?php

use Concept\Http\Message\Message;
use Concept\Http\Message\Stream\Stream;

describe('Message', function () {
    it('can get and set protocol version', function () {
        $message = new Message();
        $newMessage = $message->withProtocolVersion('2.0');
        
        expect($newMessage->getProtocolVersion())->toBe('2.0');
        expect($message->getProtocolVersion())->toBe('1.1'); // Original unchanged
    });

    it('can set and get headers', function () {
        $message = new Message();
        $newMessage = $message->withHeader('Content-Type', 'application/json');
        
        expect($newMessage->hasHeader('Content-Type'))->toBeTrue();
        expect($newMessage->getHeader('Content-Type'))->toBe(['application/json']);
    });

    it('headers are case-insensitive', function () {
        $message = new Message();
        $newMessage = $message->withHeader('Content-Type', 'text/html');
        
        expect($newMessage->hasHeader('content-type'))->toBeTrue();
        expect($newMessage->getHeader('CONTENT-TYPE'))->toBe(['text/html']);
    });

    it('can add headers', function () {
        $message = new Message();
        $newMessage = $message->withHeader('X-Custom', 'value1')
            ->withAddedHeader('X-Custom', 'value2');
        
        expect($newMessage->getHeader('X-Custom'))->toBe(['value1', 'value2']);
    });

    it('can remove headers', function () {
        $message = new Message();
        $newMessage = $message->withHeader('X-Remove', 'value')
            ->withoutHeader('X-Remove');
        
        expect($newMessage->hasHeader('X-Remove'))->toBeFalse();
    });

    it('can get header line', function () {
        $message = new Message();
        $newMessage = $message->withHeader('Accept', ['text/html', 'application/json']);
        
        expect($newMessage->getHeaderLine('Accept'))->toBe('text/html,application/json');
    });

    it('can set and get body', function () {
        $message = new Message();
        $stream = new Stream();
        $resource = fopen('php://memory', 'r+');
        fwrite($resource, 'Body Content');
        rewind($resource);
        $stream->setResource($resource);
        
        $newMessage = $message->withBody($stream);
        
        expect((string) $newMessage->getBody())->toBe('Body Content');
    });

    it('throws exception when getting body that is not set', function () {
        $message = new Message();
        $message->getBody();
    })->throws(RuntimeException::class);

    it('validates header names', function () {
        $message = new Message();
        $message->withHeader('', 'value');
    })->throws(InvalidArgumentException::class);

    it('validates header values as strings', function () {
        $message = new Message();
        $message->withHeader('X-Test', 123);
    })->throws(InvalidArgumentException::class);

    it('accepts array of header values', function () {
        $message = new Message();
        $newMessage = $message->withHeader('Accept', ['text/html', 'application/json']);
        
        expect($newMessage->getHeader('Accept'))->toBe(['text/html', 'application/json']);
    });

    it('validates array header values are strings', function () {
        $message = new Message();
        $message->withHeader('X-Test', ['valid', 123]);
    })->throws(InvalidArgumentException::class);
});
