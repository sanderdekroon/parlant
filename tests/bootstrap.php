<?php


require __DIR__.'/../vendor/autoload.php';

// Mock the 'get_posts()' function
function get_posts($arguments)
{
    return [
        new WP_Post,
        new WP_Post,
    ];
}

// Configure Parlant to only return the array of arguments.
// Obviously this is only for testing purposes.
\Sanderdekroon\Parlant\Configurator\ParlantConfigurator::globally([
    'return'        => 'argument',
    'post_status'   => 'any',
]);
