<?php

namespace Sanderdekroon\Parlant;

class ArgumentFormatter implements FormatterInterface
{

    /**
     * Return the raw arguments.
     * @param  array  $arguments
     * @return array
     */
    public function output(array $arguments)
    {
        return $arguments;
    }
}
