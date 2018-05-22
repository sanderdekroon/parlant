<?php

namespace Sanderdekroon\Parlant\Builder;

use Closure;
use InvalidArgumentException;

class WhereMetaClause
{

    protected $grammar;
    protected $relation;


    public function __construct($grammar)
    {
        $this->grammar = $grammar;
    }

    /**
     * Query the meta values (custom post fields) of posts.
     * @param  string|array|Closure $column         The field name, an array of where clauses or an Closure detailing a nested where clause.
     * @param  string       $operator
     * @param  mixed        $value
     * @param  string       $type           The type comparison, for example NUMERIC or CHAR
     * @param  string       $relation       AND/OR, currently unimplemented
     * @param  integer      $level          The query level, currently unimplemented
     * @return $this
     */
    public function build($column, $operator = null, $value = null, $type = null, $relation = null, $level = 1)
    {
        /** @todo Rewrite */
        $this->relation = [$level => empty($relation) ? 'AND' : $relation];

        // If the column is an array, we will assume it is an array of key-value pairs
        // and can add them each as a where clause. We will maintain the boolean we
        // received when the method was called and pass it into the nested where.
        if (is_array($column)) {
            return $this->addArrayOfWhereMetas($column);
        }

        // If the column parameter is a closure we'll start a nested meta query.
        if ($column instanceof Closure) {
            $nestedMetas = $this->extractNestedMetaClosures($column);
            return $this->whereNestedMeta($nestedMetas);
        }

        // Here we will make some assumptions about the operator. If only 2 values are
        // passed to the method, we will assume that the operator is an equals sign
        // and keep going. Otherwise, we'll require the operator to be passed in.
        list($value, $operator) = $this->prepareValueAndOperator(
            $value,
            $operator,
            (func_num_args() == 2 || is_null($value))
        );

        // If the given operator is not found in the list of valid operators we will
        // assume that the developer is just short-cutting the '=' operators and
        // we will set the operators to '=' and set the values appropriately.
        if ($this->invalidOperator($operator)) {
            list($value, $operator) = [$operator, '='];
        }

        // If no type is given or if it's an invalid one, we'll default back
        // to the CHAR comparing type. The $type is checked against the
        // values in the supplied posttype grammar.
        $type = $this->getValidMetaType($type);

        return [compact(
            'column',   // Metakey name
            'value',    // The actual value
            'operator', // =, <, >, etc.
            'type',     // CHAR, BINARY, etc
            'level'     // Unimplemented, undocumented.
        )];
    }

    public function getRelation()
    {
        return $this->relation;
    }

    /**
     * Resolve the closures and replace them with NestedMeta classes.
     * @param  Closure $closure
     * @return NestedMeta
     */
    protected function extractNestedMetaClosures($closure)
    {
        $nestedMeta = call_user_func($closure->bindTo(new NestedMeta));

        $query = $nestedMeta->getQuery();
        foreach ($query as $key => $meta) {
            if ($meta['column'] instanceof Closure) {
                $query[$key] = $this->extractNestedMetaClosures($meta['column']);
            }
        }

        $nestedMeta->replaceQuery($query);
        return $nestedMeta;
    }

    /**
     * Nest multiple meta queries by supplying a query. If the closure contains
     * another closure, it is resolved recursivly.
     * @param  Closure $closure
     * @return NestedMeta          Returns a NestedMeta instance which is further processed by the compiler.
     */
    protected function whereNestedMeta($nestedMeta)
    {
        if (!$nestedMeta instanceof NestedMeta) {
            throw new InvalidArgumentException('Invalid class supplied for nested meta query');
        }

        $query = $nestedMeta->getQuery();

        foreach ($query as $key => $meta) {
            if ($meta instanceof NestedMeta) {
                $query[$key] = $this->whereNestedMeta($meta);
                continue;
            }

            $query[$key] = $this->parseNestedMeta($meta);
        }

        $nestedMeta->replaceQuery($query);
        return [$nestedMeta];
    }

    /**
     * Parse the nested meta fields by validating them. If the meta fields is
     * an instance of NestedMeta, we'll resolve that recursively.
     * @param  array|NestedMeta $meta
     * @return array
     */
    protected function parseNestedMeta($meta)
    {
        if ($meta instanceof NestedMeta) {
            $meta = $this->whereNestedMeta($meta);
        }

        return $this->validateMetaFields($meta);
    }

    /**
     * Do some basic validating of the meta fields. Checks if the operator is valid and
     * if the meta type (if supplied any) is valid with the current grammar.
     * @param  array $fields
     * @return array
     */
    protected function validateMetaFields($fields)
    {
        extract($fields);

        // If the given operator is not found in the list of valid operators we will
        // assume that the developer is just short-cutting the '=' operators and
        // we will set the operators to '=' and set the values appropriately.
        if ($this->invalidOperator($operator)) {
            list($value, $operator) = [$operator, '='];
        }

        // If no type is given or if it's an invalid one, we'll default back
        // to the CHAR comparing type. The $type is checked against the
        // values in the supplied posttype grammar.
        $type = $this->getValidMetaType($type);

        return compact('column', 'value', 'operator', 'type', 'level');
    }

    /**
     * Adds arrays of where metas to the query.
     * @param array $metaArray
     */
    protected function addArrayOfWhereMetas($metaArray)
    {
        $build = [];
        foreach ($metaArray as $array) {
            $build = array_merge($build, $this->build(...$array));
        }

        return $build;
    }

    /**
     * Return a valid comparator type, like CHAR or NUMERIC.
     * @param  string $type
     * @return string       Returns 'CHAR' if none is supplied or if it's invalid
     */
    protected function getValidMetaType($type = null)
    {
        if (is_null($type) || !in_array($type, $this->grammar->getComparators())) {
            return 'CHAR';
        }

        return strtoupper($type);
    }


    protected function invalidOperator($operator)
    {
        return !in_array($operator, $this->grammar->getOperators());
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
