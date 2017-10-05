<?php

namespace Sanderdekroon\Parlant;

class ArrayFormatter implements FormatterInterface
{

    /**
     * Return an array of \WP_Posts instances.
     * @param  array  $arguments
     * @return array
     */
    public function output(array $arguments)
    {
        return get_posts($arguments);
    }
}
