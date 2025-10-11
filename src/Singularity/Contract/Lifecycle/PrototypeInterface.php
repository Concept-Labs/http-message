<?php
namespace Concept\Singularity\Contract\Lifecycle;

/**
 * PrototypeInterface for Singularity Container
 * 
 * This interface marks a class as a prototype in the Singularity Container ecosystem.
 * When Singularity Container is available, this will be replaced by the actual interface.
 */
interface PrototypeInterface
{
    /**
     * Create a new prototype instance
     * 
     * @return static
     */
    public function prototype(): static;
}
