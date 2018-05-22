<?php

namespace Sanderdekroon\Parlant\Builder;

use Closure;
use InvalidArgumentException;

trait QueriesMeta
{
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
    public function whereMeta($column, $operator = null, $value = null, $type = null, $relation = null, $level = 1)
    {
        $clause = new WhereMetaClause($this->getGrammar());

        foreach ($clause->build($column, $operator, $value, $type, $relation, $level) as $where) {
            $this->appendBinding('whereMetas', $where);
        }

        $this->setBinding('whereMetaRelation', $clause->getRelation() + ($this->getBinding('whereMetaRelation') ?: [1 => 'AND']));

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
}
