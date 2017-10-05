<?php

require 'vendor/autoload.php';

// Configure Parlant to only return the array of arguments.
// Obviously this is only for testing purposes.
$parlant = new \Sanderdekroon\Parlant\Posttype;
$parlant->configure(
    new \Sanderdekroon\Parlant\Configurator\PosttypeConfigurator([
        'return'        => 'argument',
        'post_status'   => 'any',
    ])
);
