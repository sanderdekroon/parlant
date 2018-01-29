<?php

namespace Sanderdekroon\Parlant\Builder;

use Closure;
use InvalidArgumentException;

trait QueriesMeta
{

    protected $grammar;
    
    /**
     * Query the meta values (custom post fields) of posts.
     * @param  string       $column         The field name
     * @param  string       $operator
     * @param  mixed        $value
     * @param  string       $type           The type comparison, for example NUMERIC or CHAR
     * @param  string       $relation       AND/OR, currently unimplemented
     * @param  integer      $level          The query level, currently unimplemented
     * @return $this
     */
    public function whereMeta($column, $operator = null, $value = null, $type = null, $relation = null, $level = 1)
    {
        // If the column is an array, we will assume it is an array of key-value pairs
        // and can add them each as a where clause. We will maintain the boolean we
        // received when the method was called and pass it into the nested where.
        if (is_array($column)) {
            return $this->addArrayOfWhereMetas($column);
        }

        // If the column parameter is a closure we'll start a nested meta query.
        if ($column instanceof Closure) {
            $nestedMetas = $this->extractNestedMetaClosures($column);
            $this->appendBinding('whereMetas', $this->whereNestedMeta($nestedMetas, $relation));

            return $this;
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

        if (!empty($relation)) { //Do a relation check here.
            $this->setBinding('whereMetaRelation', $this->getBinding('whereMetaRelation') ?: [] + [$level => $relation]);
        }

        $this->appendBinding('whereMetas', compact(
            'column',   // Metakey name
            'value',    // The actual value
            'operator', // =, <, >, etc.
            'type',     // CHAR, BINARY, etc
            'level'     // Unimplemented, undocumented.
        ));

        return $this;
    }

    /**
     * Query the meta values (custom post fields) of posts and set the relation to OR
     * @param  string       $column         The field name
     * @param  string       $operator
     * @param  mixed        $value
     * @param  string       $type           The type comparison, for example NUMERIC or CHAR
     * @param  integer      $level          The query level, currently unimplemented
     * @return $this
     */
    public function orWhereMeta($column, $operator = null, $value = null, $type = null, $level = 1)
    {
        return $this->whereMeta($column, $operator, $value, $type, 'OR', $level);
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
        return $nestedMeta;
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
        foreach ($metaArray as $array) {
            $this->whereMeta(...$array);
        }

        return $this;
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

    protected abstract function setBinding();
    
    protected abstract function getBinding();
    
    protected abstract function appendBinding();
    
    protected abstract function invalidOperator();
    
    protected abstract function prepareValueAndOperator();
}
