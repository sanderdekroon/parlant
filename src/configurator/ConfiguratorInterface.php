<?php

namespace Sanderdekroon\Parlant;

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
     * Dump (return) the full configuration.
     * @return array
     */
    public function dump();
}
