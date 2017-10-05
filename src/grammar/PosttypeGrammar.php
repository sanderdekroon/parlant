<?php

namespace Sanderdekroon\Parlant;

class PosttypeGrammar
{

    protected $operators = [
        '=', '!=', '>', '>=', '<', '<=',
        'LIKE', 'NOT LIKE', 'IN', 'NOT IN',
        'BETWEEN', 'NOT BETWEEN', 'NOT EXISTS',
        'REGEXP', 'NOT REGEXP', 'RLIKE',
    ];

    protected $comparators = [
        'NUMERIC', 'BINARY', 'CHAR', 'DATE', 'DATETIME',
        'DECIMAL', 'SIGNED', 'TIME', 'UNSIGNED',
    ];

    protected $relations = [
        'AND', 'OR',
    ];

    protected $taxonomyFields = [
        'term_id', 'name', 'slug', 'term_taxonomy_id',
    ];

    protected $taxonomyOperators = [
        'IN', 'NOT IN', 'AND', 'EXISTS', 'NOT EXISTS',
    ];

    protected $queryTypes = [
        'wheres', 'whereMetas', 'whereMetaRelation', 'whereTaxonomies', 'whereTaxonomyRelation', 'limit',
    ];

    protected $arguments = [
        'author', 'author_name', 'author__in', 'author__not_in', 'cat', 'category_name', 'category_name', 'category__and',
        'category__in', 'category__not_in', 'tag', 'tag_id', 'tag__and', 'tag__in', 'tag__not_in', 'tag_slug__and', 'tag_slug__in',
        'p', 'name', 'page_id', 'pagename', 'pagename', 'post_parent', 'post_parent__in', 'post_parent__not_in', 'post__in', 'post__not_in',
        'has_password', 'post_password', 'post_type', 'post_status', 'posts_per_page', 'posts_per_archive_page', 'nopaging', 'paged',
        'nopaging', 'posts_per_archive_page', 'offset', 'paged', 'page', 'ignore_sticky_posts', 'order', 'orderby', 'year',
        'monthnum', 'w', 'day', 'hour', 'minute', 'second', 'm', 'perm', 'cache_results', 'update_post_term_cache',
        'update_post_meta_cache', 'no_found_rows', 's', 'exact', 'sentence', 'fields'
    ];

    protected $argumentSynonyms = [
        'status'    => 'post_status',
        
    ];

    protected $postProperties = [
        'ID', 'post_author', 'post_name', 'post_type', 'post_title', 'post_date', 'post_date_gmt', 'post_content', 'post_excerpt', 'post_status',
        'comment_status', 'ping_status', 'post_password', 'post_parent', 'post_modified', 'post_modified_gmt', 'comment_count', 'menu_order'
    ];

    protected $formatters = [
        'array'     => 'Sanderdekroon\Parlant\ArrayFormatter',
        'argument'  => 'Sanderdekroon\Parlant\ArgumentFormatter',
        'query'     => 'Sanderdekroon\Parlant\QueryFormatter',
    ];

    /**
     * Return the query arguments that need to be processed in a method
     * @return array
     */
    public function getQueryTypes()
    {
        return $this->queryTypes;
    }

    /**
     * Return all valid operators
     * @return array
     */
    public function getOperators()
    {
        return $this->operators;
    }

    /**
     * Return all valid comparators
     * @return array
     */
    public function getComparators()
    {
        return $this->comparators;
    }

    /**
     * Return all valid WordPress query arguments
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }


    public function getFormatters()
    {
        return $this->formatters;
    }


    public function getTaxonomyFields()
    {
        return $this->taxonomyFields;
    }
    

    public function getPostProperties()
    {
        return $this->postProperties;
    }
    

    public function getTaxonomyOperators()
    {
        return $this->taxonomyOperators;
    }


    public function getFormatter($type)
    {
        if (array_key_exists($type, $this->getFormatters())) {
            return $this->formatters[$type];
        }

        return \stdClass(); //I shouldn't be doing this
    }
}
