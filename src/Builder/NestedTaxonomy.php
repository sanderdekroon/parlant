<?php

namespace Sanderdekroon\Parlant\Builder;

use BadMethodCallException;

class NestedTaxonomy
{

    protected $query = [];
    protected $taxonomy;
    protected $relation;

    /**
     * Used to determine if the developer has set a default taxonomy name.
     * @var bool
     */
    private $hasDefaultTaxonomy;

    public function __construct($taxonomyName = null)
    {
        $this->taxonomy = $taxonomyName;
        $this->hasDefaultTaxonomy = empty($taxonomyName) ? false : true;
    }

    /**
     * Base method for creating a tax_query within a nested context.
     * @param  string  $taxonomy        Name of the taxonomy.
     * @param  string  $field           The field to query (term_id, name, etc.)
     * @param  string  $operator        The operator to compare the value to (IN, NOT In, etc.)
     * @param  mixed   $value           The value of the term to query. Can be a string or an integer.
     * @param  bool    $includeChildren Whether or not to include children in hierarchical taxonomies.
     * @param  string  $relation        The relation between different subqueries within the same nested query.
     * @param  integer $level           Depth level. You should not modify this yourself.
     * @return $this
     */
    protected function where($taxonomy, $field, $operator = null, $value = null, $includeChildren = true, $relation = null, $level = 2)
    {
        if (func_num_args() == 3 || is_null($value)) {
            $value = $operator;
            $operator = 'IN';
        }

        $this->setQuery(compact(
            'taxonomy',
            'field',
            'operator',
            'value',
            'includeChildren',
            'level'
        ));

        if (!empty($relation)) {
            $this->setRelation($relation);
        }

        return $this;
    }

    /**
     * Magically call additional helpers on a nested taxonomy query. Accepted methods are named
     * after the field they're querying. For example: slug(), name(), etc. This method also
     * figures out if the developer has set a default taxonomy that we should injext.
     * @param  string $methodname       The methodname the developer is calling.
     * @param  array  $arguments        See $this->where for accepted arguments.
     * @return $this
     */
    public function __call($methodname, $arguments)
    {
        $fieldsToMethods = [
            'slug'              => 'slug',
            'name'              => 'name',
            'termTaxonomyId'    => 'term_taxonomy_id',
            'id'                => 'term_id',
        ];

        // If the developer is calling a method which we have not defined,
        // throw a BadMethodCall exception since we can't recover from this.
        if (!in_array($methodname, array_keys($fieldsToMethods))) {
            throw new BadMethodCallException('Invalid method called on NestedTaxonomy.');
        }
            
        // If the developer has not set a default taxonomy we'll grab the first  argument
        // from the method that's being called. We will assume it's the taxonomy name
        // we are looking for and set this value to the class property taxonomy.
        if ($this->hasDefaultTaxonomy === false) {
            $this->taxonomy = reset($arguments);
            unset($arguments[0]);
        }
        
        // Construct a where call with the gathered variables.
        return $this->where(
            $this->taxonomy,
            $fieldsToMethods[$methodname],
            ...$arguments
        );
    }


    public function orWhere($taxonomy, $field, $operator = null, $value = null, $includeChildren = true)
    {
        $this->setRelation('OR');
        return $this->where($taxonomy, $field, $operator, $value, $includeChildren);
    }


    public function relation($relation)
    {
        $this->setRelation($relation);
        return $this;
    }


    protected function setQuery($query)
    {
        $this->query[] = $query;
    }


    public function getQuery()
    {
        return $this->query;
    }


    protected function setRelation($relation)
    {
        $this->relation = $relation;
    }


    public function getRelation()
    {
        return empty($this->relation) ? 'AND' : $this->relation;
    }


    public function replaceQuery($query)
    {
        $this->query = $query;
    }
}
