<?php

require 'vendor/autoload.php';

// Configure Parlant to only return the array of arguments.
// Obviously this is only for testing purposes.
\Sanderdekroon\Parlant\Configurator\ParlantConfigurator::globally([
    'return'        => 'argument',
    'post_status'   => 'any',
]);
