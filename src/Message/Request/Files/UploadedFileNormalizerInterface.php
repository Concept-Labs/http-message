<?php
namespace Concept\Http\Message\Request\Files;

interface UploadedFileNormalizer
{
    /**
     * Normalize the files array.
     */
    public function normalizeFiles(array $files): array;
}
