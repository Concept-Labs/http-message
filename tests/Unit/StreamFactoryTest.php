<?php

use Concept\Http\Message\Stream\StreamFactory;
use Concept\Http\Message\Stream\Stream;

describe('StreamFactory', function () {
    beforeEach(function () {
        $this->factory = new StreamFactory(new Stream());
    });

    it('can create a stream from string', function () {
        $stream = $this->factory->createStream('Hello, World!');
        
        expect($stream->getContents())->toBe('Hello, World!');
    });

    it('can create a stream from file', function () {
        $tmpFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tmpFile, 'File Content');
        
        $stream = $this->factory->createStreamFromFile($tmpFile);
        
        expect($stream->getContents())->toBe('File Content');
        
        unlink($tmpFile);
    });

    it('throws exception when file does not exist', function () {
        @$this->factory->createStreamFromFile('/non/existent/file.txt');
    })->throws(RuntimeException::class);

    it('can create stream from resource', function () {
        $resource = fopen('php://memory', 'r+');
        fwrite($resource, 'Resource Content');
        rewind($resource);
        
        $stream = $this->factory->createStreamFromResource($resource);
        
        expect($stream->getContents())->toBe('Resource Content');
    });

    it('creates independent stream instances', function () {
        $stream1 = $this->factory->createStream('First');
        $stream2 = $this->factory->createStream('Second');
        
        expect($stream1)->not->toBe($stream2);
        expect($stream1->getContents())->toBe('First');
        $stream2->rewind();
        expect($stream2->getContents())->toBe('Second');
    });
});
