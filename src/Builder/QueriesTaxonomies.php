<?php

namespace Sanderdekroon\Parlant\Builder;

use Closure;
use InvalidArgumentException;

trait QueriesTaxonomies
{
    public function whereTaxonomy($taxonomy, $field = null, $operator = null, $value = null, $includeChildren = true, $relation = null, $level = 1)
    {
        $clause = new WhereTaxonomyClause($this->getGrammar());

        foreach ($clause->build($taxonomy, $field, $operator, $value, $includeChildren, $relation, $level) as $where) {
            $this->appendBinding('whereTaxonomies', $where);
        }

        $this->setBinding('whereTaxonomyRelation', $clause->getRelation() + ($this->getBinding('whereTaxonomyRelation') ?: [1 => 'AND']));

        return $this;
    }

    /**
     * Query the meta values (custom post fields) of posts and set the relation to OR
     * @param  string       $taxonomy         The field name
     * @param  string       $operator
     * @param  mixed        $value
     * @param  string       $type           The type comparison, for example NUMERIC or CHAR
     * @param  integer      $level          The query level, currently unimplemented
     * @return $this
     */
    public function orWhereTaxonomy($taxonomy, $field = null, $operator = null, $value = null, $includeChildren = true, $level = 1)
    {
        return $this->whereTaxonomy($taxonomy, $field, $operator, $value, $includeChildren, 'OR', $level);
    }
}
