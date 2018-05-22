<?php

namespace Sanderdekroon\Parlant\Builder;

use Closure;
use InvalidArgumentException;

class WhereTaxonomyClause
{

    protected $grammar;
    protected $relation;


    public function __construct($grammar)
    {
        $this->grammar = $grammar;
    }


    public function build($taxonomy, $field = null, $operator = null, $value = null, $includeChildren = true, $relation = null, $level = 1)
    {
        /** @todo Rewrite */
        $this->relation = [$level => empty($relation) ? 'AND' : $relation];

        // If the taxonomy is an array, we will assume it is an array of key-value pairs
        // and can add them each as a where clause. We will maintain the boolean we
        // received when the method was called and pass it into the nested where.
        if (is_array($taxonomy)) {
            return $this->addArrayOfWhereTaxonomies($taxonomy);
        }

        // If the taxonomy parameter is a close we'll start a nested meta query.
        if ($taxonomy instanceof Closure) {
            $nestedTaxonomy = $this->extractNestedTaxonomyClosures($taxonomy);
            
            return [$this->whereNestedTaxonomy($nestedTaxonomy, $relation)];
        }

        // If the field variable is a closure, we'll start a nested taxonomy query
        // and use the supplied taxonomy as default taxonomy in the query.
        if ($field instanceof Closure) {
            $nestedTaxonomy = $this->extractNestedTaxonomyClosures($field, $taxonomy);
            
            return [$this->whereNestedTaxonomy($nestedTaxonomy, $relation)];
        }

        // Here we will make some assumptions about the operator. If only 3 values are
        // passed to the method, we will assume that the operator is an equals sign
        // and keep going. Otherwise, we'll require the operator to be passed in.
        list($value, $operator) = $this->prepareValueAndOperator(
            $value,
            $operator,
            (func_num_args() == 3 || is_null($value)),
            true // This is needed since tax_query has different default operator.
        );

        // If the given operator is not found in the list of valid operators we will
        // assume that the developer is just short-cutting the '=' operators and
        // we will set the operators to '=' and set the values appropriately.
        if ($this->invalidOperator($operator)) {
            list($value, $operator) = [$operator, 'IN'];
        }

        // Validate the supplied field against the known fields of an taxonomy
        // query within WP_Query. If no valid field is found, we'll default
        // back to the WordPress default which is term_id.
        $field = $this->getValidTermField($field);

        return [compact(
            'taxonomy',             // Taxonomy name
            'field',                // Taxonomy term field
            'operator',             // The term operator
            'value',                // The value of the term
            'includeChildren',      // Include/exclude children
            'level'                 // Unimplemented, undocumented.
        )];

        return $this;
    }


    public function getRelation()
    {
        return $this->relation;
    }

    /**
     * Resolve the closures and replace them with NestedTaxonomy classes.
     * @param  Closure $closure
     * @return NestedTaxonomy
     */
    protected function extractNestedTaxonomyClosures($closure, $taxonomyName = null)
    {
        $nestedTaxonomy = call_user_func($closure->bindTo(new NestedTaxonomy($taxonomyName)));

        $query = $nestedTaxonomy->getQuery();
        foreach ($query as $key => $taxonomy) {
            if ($taxonomy['taxonomy'] instanceof Closure) {
                $query[$key] = $this->extractNestedTaxonomyClosures($taxonomy['taxonomy']);
            }
        }

        $nestedTaxonomy->replaceQuery($query);
        return $nestedTaxonomy;
    }

    /**
     * Nest multiple taxonomy queries by supplying a query. If the closure contains
     * another closure, it is resolved recursivly.
     * @param  Closure $closure
     * @return NestedTaxonomy          Returns a NestedTaxonomy instance which is further processed by the compiler.
     */
    protected function whereNestedTaxonomy($nestedTaxonomy)
    {
        if (!$nestedTaxonomy instanceof NestedTaxonomy) {
            throw new InvalidArgumentException('Invalid class supplied for nested taxonomy query');
        }

        $query = $nestedTaxonomy->getQuery();

        foreach ($query as $key => $taxonomy) {
            if ($taxonomy instanceof NestedTaxonomy) {
                $query[$key] = $this->whereNestedTaxonomy($taxonomy);
                continue;
            }

            $query[$key] = $this->parseNestedTaxonomy($taxonomy);
        }

        $nestedTaxonomy->replaceQuery($query);

        return $nestedTaxonomy;
    }

    /**
     * Parse the nested taxonomy fields by validating them. If the taxonomy fields is
     * an instance of NestedTaxonomy, we'll resolve that recursively.
     * @param  array|NestedTaxonomy $taxonomy
     * @return array
     */
    protected function parseNestedTaxonomy($taxonomy)
    {
        if ($taxonomy instanceof NestedTaxonomy) {
            $taxonomy = $this->whereNestedTaxonomy($taxonomy);
        }

        return $this->validateTaxonomyFields($taxonomy);
    }

    /**
     * Do some basic validating of the meta fields. Checks if the operator is valid and
     * if the meta type (if supplied any) is valid with the current grammar.
     * @param  array $fields
     * @return array
     */
    protected function validateTaxonomyFields($fields)
    {
        extract($fields);

        // If the given operator is not found in the list of valid operators we will
        // assume that the developer is just short-cutting the '=' operators and
        // we will set the operators to '=' and set the values appropriately.
        if ($this->invalidOperator($operator)) {
            list($value, $operator) = [$operator, '='];
        }

        // If no field is given or if it's an invalid one, we'll default back
        // to the term_id field. The $field is checked against the
        // values in the supplied posttype grammar.
        $field = $this->getValidTermField($field);

        return compact('taxonomy', 'field', 'operator', 'value', 'includeChildren', 'level');
    }

    /**
     * Adds arrays of where metas to the query.
     * @param array $taxonomyArray
     */
    protected function addArrayOfWhereTaxonomies($taxonomyArray)
    {
        $build = [];
        foreach ($taxonomyArray as $array) {
            $build[] = $this->build(...$array);
        }

        return $build;
    }

    /**
     * Return a valid taxonomy term field, like term_id or term_slug.
     * @param  string $type
     * @return string       Returns 'term_id' if none is supplied or if it's invalid
     */
    protected function getValidTermField($field = null)
    {
        if (is_null($field) || !in_array($field, $this->grammar->getTaxonomyFields())) {
            return 'term_id';
        }

        return strtolower($field);
    }

    // protected abstract function setBinding($key, $data);
    
    // protected abstract function getBinding($key);

    // protected abstract function getGrammar();
    
    // protected abstract function appendBinding($key, $data);
    
    protected function invalidOperator($operator)
    {
        return !in_array($operator, $this->grammar->getTaxonomyOperators());
    }
    
    protected function prepareValueAndOperator($value, $operator, $useDefault = false, $termDefault = false)
    {
        if ($useDefault) {
            return [$operator, $termDefault ? 'IN' : '='];
        }

        if ($this->invalidOperator($operator) && !is_null($value)) {
            throw new InvalidArgumentException('Illegal operator and value combination.');
        }

        return [$value, $operator];
    }
}
