<?php

use Concept\Http\Message\Stream\Stream;

describe('Stream', function () {
    it('can be created and set a resource', function () {
        $stream = new Stream();
        $resource = fopen('php://memory', 'r+');
        fwrite($resource, 'Hello, World!');
        rewind($resource);
        
        $stream->setResource($resource);
        
        expect($stream->getSize())->toBeGreaterThan(0);
        expect($stream->isReadable())->toBeTrue();
        expect($stream->isSeekable())->toBeTrue();
    });

    it('can read content', function () {
        $stream = new Stream();
        $resource = fopen('php://memory', 'r+');
        fwrite($resource, 'Test Content');
        rewind($resource);
        $stream->setResource($resource);
        
        expect($stream->getContents())->toBe('Test Content');
    });

    it('can write content', function () {
        $stream = new Stream();
        $resource = fopen('php://memory', 'r+');
        $stream->setResource($resource);
        
        $stream->write('New Content');
        $stream->rewind();
        
        expect($stream->getContents())->toBe('New Content');
    });

    it('can be converted to string', function () {
        $stream = new Stream();
        $resource = fopen('php://memory', 'r+');
        fwrite($resource, 'String Content');
        rewind($resource);
        $stream->setResource($resource);
        
        expect((string) $stream)->toBe('String Content');
    });

    it('can seek and tell position', function () {
        $stream = new Stream();
        $resource = fopen('php://memory', 'r+');
        fwrite($resource, '0123456789');
        rewind($resource);
        $stream->setResource($resource);
        
        expect($stream->tell())->toBe(0);
        $stream->seek(5);
        expect($stream->tell())->toBe(5);
    });

    it('can detect EOF', function () {
        $stream = new Stream();
        $resource = fopen('php://memory', 'r+');
        fwrite($resource, 'test');
        rewind($resource);
        $stream->setResource($resource);
        
        expect($stream->eof())->toBeFalse();
        $stream->getContents(); // Read to end
        expect($stream->eof())->toBeTrue();
    });

    it('can be detached', function () {
        $stream = new Stream();
        $resource = fopen('php://memory', 'r+');
        $stream->setResource($resource);
        
        $detached = $stream->detach();
        
        expect($detached)->toBe($resource);
        expect($stream->getMetadata())->toBeNull();
    });

    it('can be closed', function () {
        $stream = new Stream();
        $resource = fopen('php://memory', 'r+');
        $stream->setResource($resource);
        
        $stream->close();
        
        expect(is_resource($resource))->toBeFalse();
    });

    it('throws exception when reading from detached stream', function () {
        $stream = new Stream();
        $resource = fopen('php://memory', 'r+');
        $stream->setResource($resource);
        $stream->detach();
        
        $stream->getContents();
    })->throws(RuntimeException::class);

    it('throws exception when writing to detached stream', function () {
        $stream = new Stream();
        $resource = fopen('php://memory', 'r+');
        $stream->setResource($resource);
        $stream->detach();
        
        $stream->write('test');
    })->throws(RuntimeException::class);
});
