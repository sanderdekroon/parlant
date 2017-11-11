<?php

namespace Sanderdekroon\Parlant\Configurator;

use Sanderdekroon\Parlant\Container;

class PosttypeConfigurator implements ConfiguratorInterface
{

    protected static $global;

    protected $local = [];

    protected $default = [
        'posts_per_page'    => -1,
        'post_type'         => 'any',
        // 'post_status'      => 'publish', @todo may change this to 'any'
        'return'            => 'array',
    ];


    public function __construct($settings = null)
    {
        // If the developer did not supply an instance of the container, we create a new instance
        // and add the default configuration to it
        if (is_null($settings) || is_array($settings)) {
            self::$global = new Container;
            $this->add($this->defaultConfiguration());
        }

        // We're assuming the developer is passing configuration directly into the configurator
        // Since we're not sure if the default configuration is applied, we'll try to
        // merge it with the existing configuration.
        if (is_array($settings)) {
            $this->addArrayOfSettings(array_merge($this->defaultConfiguration(), $settings));
        }
    }

    /**
     * Get an setting from the settings container
     * @param  string $name
     * @return mixed
     */
    public function get($name)
    {
        if ($this->hasLocal($name)) {
            return $this->getLocal($name);
        }

        return self::$global->get($name);
    }


    protected function hasLocal($name)
    {
        return array_key_exists($name, $this->local);
    }


    protected function getLocal($name)
    {
        return $this->local[$name];
    }

    /**
     * Add an setting to the settings container
     * @param string $name
     * @param mixed $setting
     */
    public function add($name, $setting = null)
    {
        if (is_array($name)) {
            return $this->addArrayOfSettings($name);
        }

        // Validate setting names?

        return self::$global->bind($name, $setting);
    }


    public function addLocal($name, $setting = null)
    {
        return $this->local[$name] = $setting;
    }

    /**
     * Check if the current configuration contains a setting called $name
     * @param  string  $name
     * @return boolean
     */
    public function has($name)
    {
        return self::$global->has($name);
    }

    /**
     * Add an array of settings to the settings container
     * @param array $settings
     */
    public function addArrayOfSettings($settings)
    {
        foreach ($settings as $name => $setting) {
            $this->add($name, $setting);
        }
    }


    protected function defaultConfiguration()
    {
        return $this->default;
    }

    /**
     * Dump the container.
     * @return ContainerInterface
     * @todo   Remove on production
     */
    public function dump()
    {
        return self::$global;
    }
}
