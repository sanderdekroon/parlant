<?php

namespace Sanderdekroon\Parlant\Builder;

trait BuildsQueries
{
    /**
     * Get the posts by passing the bindings, configuration and building the arguments.
     * @return mixed
     */
    public function get()
    {
        return $this->getCompiler()
            ->bind($this->getBindings())
            ->withConfiguration($this->getConfiguration())
            ->build();
    }

    /**
     * Return only the first post.
     * @return \WP_Post
     */
    public function first()
    {
        $this->setBinding('posts_per_page', 1);
        $posts = $this->get();

        // If the developer has requested the argument list, we'll return the full array.
        if ($this->getConfiguration()->get('return') == 'argument') {
            return $posts;
        }

        // If it's an array, we'll assume it's one post wrapped within an array.
        // Anything else just gets returned without modification.
        return is_array($posts) ? reset($posts) : $posts;
    }

    /**
     * Return all posts with no limit.
     * @return mixed
     */
    public function all()
    {
        $this->setBinding('posts_per_page', -1);
        return $this->get();
    }

    /**
     * Count the total of found posts.
     * @return int
     */
    public function count()
    {
        $this->getConfiguration()->add('return', 'Sanderdekroon\Parlant\Formatter\CountFormatter');
        return $this->get();
    }

    /**
     * Pluck the values of the supplied column name
     * @return array
     */
    public function pluck(string $columnname)
    {
        if (!in_array($columnname, $this->getGrammar()->getPostProperties())) {
            throw new \InvalidArgumentException('Invalid columnname '.$columnname);
        }

        $this->getConfiguration()->add('return', 'Sanderdekroon\Parlant\Formatter\ArrayFormatter');

        return array_map(function ($post) use ($columnname) {
            return $post->$columnname;
        }, $this->get());
    }

    /**
     * @todo  Implement
     */
    public function avg()
    {
        throw new \BadMethodCallException('Accessing unimplemented method.');
    }

    /**
     * @todo  Implement
     */
    public function max()
    {
        throw new \BadMethodCallException('Accessing unimplemented method.');
    }

    /**
     * @todo  Implement
     */
    public function min()
    {
        throw new \BadMethodCallException('Accessing unimplemented method.');
    }


    protected abstract function setBinding($key, $data);

    protected abstract function getGrammar();
    
    protected abstract function getCompiler();
    
    protected abstract function getBindings();
    
    protected abstract function getConfiguration();
}
