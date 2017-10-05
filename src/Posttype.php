<?php

namespace Sanderdekroon\Parlant;

use Sanderdekroon\Parlant\Builder\PosttypeBuilder;
use Sanderdekroon\Parlant\Builder\BuilderInterface;

class Posttype
{

    protected static $builder;

    /**
     * Set the Posttype builder.
     * @param BuilderInterface|null $builder
     */
    public function __construct(BuilderInterface $builder = null)
    {
        self::$builder ?: (self::$builder = $builder ?: new PosttypeBuilder); //I'm sorry
    }

    /**
     * Entry method (for now) which instantiates the builder and passes the type.
     * @param  string $type
     * @return PosttypeBuilder
     */
    public static function type($type)
    {
        return (new self())->builder()->type($type);
    }


    public static function any()
    {
        return self::type('any');
    }

    /**
     * Return the saved instance of the builder.
     * @return PosttypeBuilder
     */
    private function builder()
    {
        return self::$builder;
    }

    /**
     * Direct every call to the PosttypeBuilder class
     * @param  string $method
     * @param  array $paramaters
     * @return mixed
     */
    public function __call($method, $paramaters)
    {
        if (method_exists($this, $method)) {
            return $this->$method(...$paramaters);
        }

        return $this->builder()->$method(...$paramaters);
    }
}
