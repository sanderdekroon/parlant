<?php

namespace Sanderdekroon\Parlant\Configurator;

use Sanderdekroon\Parlant\Container;

class ParlantConfigurator implements ConfiguratorInterface
{
    /**
     * All local settings which will only be applied to the current query.
     * @var array
     */
    protected $local = [];

    /**
     * Global settings that are applied to all queries.
     * @var array
     */
    protected static $global = [
        'posts_per_page'    => -1,
        'post_type'         => 'any',
        'post_status'       => 'publish',
        'return'            => 'array',
    ];

    /**
     * Get a setting by it's name. Prefers local settings to global settings.
     * @param  string $name
     * @return mixed
     */
    public function get($name)
    {
        if ($this->hasLocal($name)) {
            return $this->getLocal($name);
        }

        return $this->getGlobal($name);
    }

    /**
     * Check if a setting is set in the configurator.
     * @param  string  $name
     * @return bool
     */
    public function has($name)
    {
        if ($this->hasLocal($name) || $this->hasGlobal($name)) {
            return true;
        }

        return false;
    }

    /**
     * Set a (or multiple) global settings.
     * @param  string|array $name  Supply an array to add multiple settings.
     * @param  mixed        $value
     * @return bool
     */
    public static function globally($name, $value = null)
    {
        if (is_array($name)) {
            return self::addArrayOfGlobalSettings($name);
        }

        return self::$global[$name] = $value;
    }

    /**
     * Add a local setting.
     * @param  string|array $name  Supply an array to add multiple local settings.
     * @param  mixed        $value
     * @return $this
     */
    public function add($name, $value = null)
    {
        if (is_array($name)) {
            return $this->addArrayOfLocalSettings($name);
        }

        $this->local[$name] = $value;
        return $this;
    }

    /**
     * Add an array of global settings.
     * @param array $settings
     */
    protected static function addArrayOfGlobalSettings(array $settings)
    {
        foreach ($settings as $name => $value) {
            self::$global[$name] = $value;
        }

        return true;
    }

    /**
     * Add an array of local settings.
     * @param array $settings
     */
    protected function addArrayOfLocalSettings($settings)
    {
        foreach ($settings as $name => $value) {
            $this->add($name, $value);
        }

        return true;
    }

    /**
     * Check if a setting exists in the local property.
     * @param  string  $name
     * @return bool
     */
    protected function hasLocal($name)
    {
        return array_key_exists($name, $this->local);
    }

    /**
     * Check if a setting exists in the global settings property.
     * @param  string  $name
     * @return bool
     */
    protected function hasGlobal($name)
    {
        return array_key_exists($name, self::$global);
    }

    /**
     * Get a setting from the local settings property.
     * @param  string $name
     * @return mixed
     */
    protected function getLocal($name)
    {
        return $this->local[$name];
    }

    /**
     * Get a setting from the global settings property.
     * @param  string $name
     * @return mixed
     */
    protected function getGlobal($name)
    {
        return self::$global[$name];
    }

    /**
     * Merge the global and local settings and return the result.
     * The local settings override the global settings.
     * @return array
     */
    public function dump()
    {
        return array_merge(self::$global, $this->local);
    }
}
