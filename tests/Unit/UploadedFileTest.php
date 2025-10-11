<?php

use Concept\Http\Message\Request\Files\UploadedFile;
use Concept\Http\Message\Stream\Stream;

describe('UploadedFile', function () {
    it('can get stream', function () {
        $file = new UploadedFile();
        $stream = new Stream();
        $resource = fopen('php://memory', 'r+');
        fwrite($resource, 'File content');
        rewind($resource);
        $stream->setResource($resource);
        
        $newFile = $file->withStream($stream);
        
        expect((string) $newFile->getStream())->toBe('File content');
    });

    it('can get size', function () {
        $file = new UploadedFile();
        $newFile = $file->withSize(1024);
        
        expect($newFile->getSize())->toBe(1024);
    });

    it('can get error code', function () {
        $file = new UploadedFile();
        $newFile = $file->withError(UPLOAD_ERR_OK);
        
        expect($newFile->getError())->toBe(UPLOAD_ERR_OK);
    });

    it('can get client filename', function () {
        $file = new UploadedFile();
        $newFile = $file->withClientFilename('document.pdf');
        
        expect($newFile->getClientFilename())->toBe('document.pdf');
    });

    it('can get client media type', function () {
        $file = new UploadedFile();
        $newFile = $file->withClientMediaType('application/pdf');
        
        expect($newFile->getClientMediaType())->toBe('application/pdf');
    });

    it('can move to target path', function () {
        $file = new UploadedFile();
        $stream = new Stream();
        $resource = fopen('php://memory', 'r+');
        fwrite($resource, 'Move test');
        rewind($resource);
        $stream->setResource($resource);
        
        $newFile = $file->withStream($stream);
        
        $tmpFile = tempnam(sys_get_temp_dir(), 'upload_test');
        $newFile->moveTo($tmpFile);
        
        expect(file_get_contents($tmpFile))->toBe('Move test');
        unlink($tmpFile);
    });

    it('throws exception when moving already moved file', function () {
        $file = new UploadedFile();
        $stream = new Stream();
        $resource = fopen('php://memory', 'r+');
        fwrite($resource, 'Test');
        rewind($resource);
        $stream->setResource($resource);
        
        $newFile = $file->withStream($stream);
        
        $tmpFile1 = tempnam(sys_get_temp_dir(), 'test1');
        $tmpFile2 = tempnam(sys_get_temp_dir(), 'test2');
        
        $newFile->moveTo($tmpFile1);
        $newFile->moveTo($tmpFile2);
        
        unlink($tmpFile1);
        unlink($tmpFile2);
    })->throws(RuntimeException::class);

    it('throws exception for invalid target path', function () {
        $file = new UploadedFile();
        $stream = new Stream();
        $resource = fopen('php://memory', 'r+');
        $stream->setResource($resource);
        
        $newFile = $file->withStream($stream);
        $newFile->moveTo('');
    })->throws(InvalidArgumentException::class);

    it('throws exception when stream not set', function () {
        $file = new UploadedFile();
        $file->getStream();
    })->throws(RuntimeException::class);
});
