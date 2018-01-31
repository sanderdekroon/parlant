<?php

namespace Sanderdekroon\Parlant\Builder;

use InvalidArgumentException;

class WhereClause
{

    protected $grammar;

    public function __construct($grammar)
    {
        $this->grammar = $grammar;
    }

    /**
     * Build a where clause from the supplied column, operator and value.
     * @param  string|array $column
     * @param  string       $operator
     * @param  string|null  $value
     * @return array
     */
    public function build($column, $operator, $value)
    {
        // If the column is an array, we will assume it is an array of key-value pairs
        // and can add them each as a where clause.
        if (is_array($column)) {
            return $this->buildArrayOfWheres($column);
        }

        // Here we will make some assumptions about the operator. If only 2 values are
        // passed to the method, we will assume that the operator is an equals sign
        // and keep going. Otherwise, we'll require the operator to be passed in.
        list($value, $operator) = $this->prepareValueAndOperator(
            $value,
            $operator,
            (func_num_args() == 2 || is_null($value)) //Is this the best solution?
        );

        // If the given operator is not found in the list of valid operators we will
        // assume that the developer is just short-cutting the '=' operators and
        // we will set the operators to '=' and set the values appropriately.
        if ($this->invalidOperator($operator)) {
            list($value, $operator) = [$operator, '='];
        }

        return [compact('column', 'operator', 'value')];
    }

    /**
     * When an array was supplied for where's, we'll loop through it and use the member
     * arrays as if it were arguments to the build method.
     * @param  array $wheres
     * @return array
     */
    protected function buildArrayOfWheres($wheres)
    {
        $build = [];
        foreach ($wheres as $where) {
            list($column, $operator, $value) = $this->extractWhereValuesFromArray($where);
            $build = array_merge($build, $this->build($column, $operator, $value));
        }

        return $build;
    }

    /**
     * A where does not have to have three arguments. If two are supplied, that's allright.
     * This method makes sure no warnings are emitted and the missing values are set to null.
     * @param  array $array
     * @return array
     */
    protected function extractWhereValuesFromArray($array)
    {
        return [
            isset($array[0]) ? $array[0] : null,
            isset($array[1]) ? $array[1] : null,
            isset($array[2]) ? $array[2] : null,
        ];
    }

    /**
     * Prepare the value and operator. If $useDefault is true, return the default operator (=)
     * Throws an exception if the operator is not supported with the current grammer.
     * @param  mixed        $value
     * @param  string       $operator
     * @param  boolean      $useDefault
     * @throws InvalidArgumentException
     * @return array
     */
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

    /**
     * Determine if an operator is invalid or unsupported
     * @param  string $operator
     * @return bool
     */
    protected function invalidOperator($operator)
    {
        return !in_array($operator, $this->grammar->getOperators());
    }
}
