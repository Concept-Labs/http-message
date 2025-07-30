<?php
namespace Concept\Http\Message\Stream;

interface StreamInterface extends \Psr\Http\Message\StreamInterface
{
    /**
     * Set the resource for the stream.
     *
     * @param resource $resource
     * @return StreamInterface
     */
    public function setResource($resource): StreamInterface;

    /**
     * Get the size of the stream.
     *
     * @return int|null
     */
    public function getSize(): ?int;
}