<?php

namespace Sanderdekroon\Parlant;

trait CompilesTaxonomies
{

    /**
     * Compile the meta query to valid query arguments
     * @param  array $wheres
     */
    protected function compileWhereTaxonomies($wheres)
    {
        if (empty($wheres) || !is_array($wheres)) {
            return;
        }

        $compiled = [];

        foreach ($wheres as $where) {
            if ($where instanceof NestedTaxonomy) {
                $compiled[] = $this->compileNestedTaxonomy($where);
                continue;
            }

            $compiled[] = $this->prepareTaxonomyArguments($where);
        }

        $this->addArgument('tax_query', $compiled, true);
    }

    /**
     * Complie nested meta query arguments to valid query arguments. If a meta query
     * contains another nested meta, we'll resolve that recursively.
     * @param  NestedTaxonomy  $nestedMeta
     * @param  integer $level
     * @return array
     */
    protected function compileNestedTaxonomy($nestedTaxonomy, $level = 2)
    {
        $query = $nestedTaxonomy->getQuery();
        foreach ($query as $key => $taxonomy) {
            if ($taxonomy instanceof NestedTaxonomy) {
                $query[$key] = $this->compileNestedTaxonomy($taxonomy, $level++);
                continue;
            }
            
            $query[$key] = $this->prepareTaxonomyArguments($taxonomy);
        }
        $query['relation'] = $nestedTaxonomy->getRelation();
        
        return $query;
    }

    /**
     * Format the supplied arguments to WordPress arguments
     * @param  array $meta
     * @return array
     */
    private function prepareTaxonomyArguments($taxonomy)
    {
        return [
            'taxonomy'          => $taxonomy['taxonomy'],
            'field'             => $taxonomy['field'],
            'terms'             => $taxonomy['value'],
            'include_children'  => $taxonomy['includeChildren'],
            'operator'          => $taxonomy['operator'],
        ];
    }

    /**
     * Compile the relation(s) between meta queries and add it to the arguments.
     * @param  array $relations  The index of the array is used as the level.
     */
    protected function compileWhereTaxonomyRelation($relations)
    {
        return $this->addArgument('tax_query', ['relation' => reset($relations)], true);
    }
}
