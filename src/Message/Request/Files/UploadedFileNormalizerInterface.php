<?php
namespace Concept\Http\Message\Request\Files;

interface UploadedFileNormalizerInterface
{
    /**
     * Normalize the files array.
     */
    public function normalizeFiles(array $files): array;
}
