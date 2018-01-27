<?php

namespace Sanderdekroon\Parlant\Formatter;

use WP_Query;

class QueryFormatter implements FormatterInterface
{
    /**
     * Return an instance of WP_Query
     * @param  array  $arguments
     * @return WP_Query
     */
    public function output(array $arguments)
    {
        $query = new WP_Query($arguments);

        return $query;
    }
}
