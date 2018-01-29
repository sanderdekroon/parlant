<?php

namespace Sanderdekroon\Parlant\Compiler;

use Sanderdekroon\Parlant\Builder\NestedMeta;

trait CompilesMeta
{

    /**
     * Compile the meta query to valid query arguments
     * @param  array $wheres
     */
    protected function compileWhereMetas($wheres)
    {
        if (empty($wheres) || !is_array($wheres)) {
            return;
        }

        $compiled = [];

        foreach ($wheres as $where) {
            if ($where instanceof NestedMeta) {
                $compiled[] = $this->compileNestedMeta($where);
                continue;
            }

            $compiled[] = $this->prepareMetaArguments($where);
        }

        $this->addArgument('meta_query', $compiled, true);
    }

    /**
     * Complie nested meta query arguments to valid query arguments. If a meta query
     * contains another nested meta, we'll resolve that recursively.
     * @param  NestedMeta  $nestedMeta
     * @param  integer $level
     * @return array
     */
    protected function compileNestedMeta($nestedMeta, $level = 2)
    {
        $query = $nestedMeta->getQuery();
        foreach ($query as $key => $meta) {
            if ($meta instanceof NestedMeta) {
                $query[$key] = $this->compileNestedMeta($meta, $level++);
                continue;
            }
            
            $query[$key] = $this->prepareMetaArguments($meta);
        }
        $query['relation'] = $nestedMeta->getRelation();
        
        return $query;
    }

    /**
     * Format the supplied arguments to WordPress arguments
     * @param  array $meta
     * @return array
     */
    private function prepareMetaArguments($meta)
    {
        return [
            'key'       => $meta['column'],
            'value'     => $meta['value'],
            'compare'   => $meta['operator'],
            'type'      => $meta['type'],
        ];
    }

    /**
     * Compile the relation(s) between meta queries and add it to the arguments.
     * @param  array $relations  The index of the array is used as the level.
     */
    protected function compileWhereMetaRelation($relations)
    {
        return $this->addArgument('meta_query', ['relation' => reset($relations)], true);
    }

    protected abstract function addArgument();
}
