<?php
namespace Concept\Http\Message\Request\Files;

use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class UploadedFileNormalizer
{
    protected UploadedFileFactoryInterface $uploadedFileFactory;
    protected StreamFactoryInterface $streamFactory;

    /**
     * Dependency injection constructor.
     */
    public function __construct(
        UploadedFileFactoryInterface $uploadedFileFactory,
        StreamFactoryInterface $streamFactory
    ) {
        $this->uploadedFileFactory = $uploadedFileFactory;
        $this->streamFactory = $streamFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function normalizeFiles(array $files): array
    {
        $normalized = [];

        foreach ($files as $key => $value) {
            if (is_array($value['error'])) {
                $normalized[$key] = $this->normalizeFileArray($value);
            } else {
                $normalized[$key] = $this->createUploadedFileInstance($value);
            }
        }

        return $normalized;
    }

    /**
     * Normalize the file array.
     * 
     * @param array $file
     * 
     * @return array
     */
    protected function normalizeFileArray(array $file): array
    {
        $normalized = [];

        foreach (array_keys($file['error']) as $key) {
            $normalized[$key] = $this->createUploadedFileInstance([
                'name' => $file['name'][$key],
                'type' => $file['type'][$key],
                'tmp_name' => $file['tmp_name'][$key],
                'error' => $file['error'][$key],
                'size' => $file['size'][$key],
            ]);
        }

        return $normalized;
    }

    /**
     * Create an uploaded file instance.
     * 
     * @param array $file
     * 
     * @return UploadedFileInterface
     */
    protected function createUploadedFileInstance(array $file): UploadedFileInterface
    {
        return $this->uploadedFileFactory->createUploadedFile(
            $this->streamFactory->createStreamFromFile($file['tmp_name']),
            (int) $file['size'],
            (int) $file['error'],
            $file['name'],
            $file['type']
        );
    }
}
