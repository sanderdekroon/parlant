<?php

namespace Sanderdekroon\Parlant\Configurator;

interface ConfiguratorInterface
{

    /**
     * Return a configuration
     * @param  string $name
     * @return mixed
     */
    public function get($name);
    
    /**
     * Add a configuration
     * @param string    $name
     * @param mixed     $setting
     */
    public function add($name, $setting);

    /**
     * Dump the configuration. Remove on production.
     * @return array
     */
    public function dump();
}
