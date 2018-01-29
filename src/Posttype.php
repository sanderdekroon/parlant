<?php

namespace Sanderdekroon\Parlant;

use Sanderdekroon\Parlant\Builder\PosttypeBuilder;
use Sanderdekroon\Parlant\Builder\BuilderInterface;

class Posttype
{
    /**
     * The PosttypeBuilder of the current query.
     * @var PosttypeBuilder
     */
    protected static $builder;

    /**
     * Set the post type in the newly created builder and return the builder.
     * Since this is the start of a new query, a new instance of the
     * PosttypeBuilder is created and set as a static property.
     * @param  string $type
     * @return PosttypeBuilder
     */
    public static function type($type)
    {
        return (new self())->builder()->type($type);
    }

    /**
     * Return posts of any post type.
     * @return PosttypeBuilder
     */
    public static function any()
    {
        return self::type('any');
    }

    /**
     * Find a post by it's id.
     * @param  int $postId
     * @return WP_Post
     */
    public static function find($postId)
    {
        return self::any()->where('p', (int)$postId)->first();
    }

    /**
     * Shortcut for returning all published posts (depending on configuration) within a posttype.
     * @param  string $posttype
     * @return mixed
     */
    public static function all($posttype)
    {
        return self::type($posttype)->all();
    }

    /**
     * Create a new instance of the builder, set it as static property and return it.
     * This makes sure that a new builder is created when a new query is created.
     * @return PosttypeBuilder
     */
    private function builder()
    {
        self::$builder = new PosttypeBuilder;
        return self::$builder;
    }

    /**
     * Direct every call to the PosttypeBuilder class. It uses the static property
     * so that any previous query argument is preserved and applied at the end.
     * @param  string $method
     * @param  array $paramaters
     * @return mixed
     */
    public function __call($method, $paramaters)
    {
        if (method_exists($this, $method)) {
            return $this->$method(...$paramaters);
        }

        return self::$builder->$method(...$paramaters);
    }
}
