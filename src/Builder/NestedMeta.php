<?php

namespace Sanderdekroon\Parlant\Builder;

class NestedMeta
{

    protected $query = [];
    protected $relation;

    public function where($column, $operator = null, $value = null, $type = null, $relation = null, $level = 2)
    {
        if (func_num_args() == 2 || is_null($value)) {
            $value = $operator;
            $operator = '=';
        }

        $this->setQuery(compact(
            'column',
            'value',
            'operator',
            'type',
            'level'
        ));

        if (!empty($relation)) {
            $this->setRelation($relation);
        }

        return $this;
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
