<?php

namespace Sanderdekroon\Parlant\Formatter;

use WP_Query;

class CountFormatter implements FormatterInterface
{

    /**
     * Return the number of found posts
     * @param  array  $arguments
     * @return int
     */
    public function output(array $arguments)
    {
        return (new WP_Query($arguments))->found_posts;
    }
}
