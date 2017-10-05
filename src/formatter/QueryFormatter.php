<?php

namespace Sanderdekroon\Parlant;

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
        // if ($query->have_posts()) {
        //     return $query;
        // }

        // return false;
        return $query;
    }
}
