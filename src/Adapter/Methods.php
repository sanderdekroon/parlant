<?php

namespace Sanderdekroon\Parlant\Adapter;

class Methods
{
    protected $supportedMethods = [
        'author' => [
            'validatesAs'       => 'string',
            'translatesAs'      => 'author',
        ],
        'authorName' => [
            'validatesAs'       => 'string',
            'translatesAs'      => 'author_name',
        ],
        'authorIn' => [
            'validatesAs'       => 'array',
            'translatesAs'      => 'author__in',
        ],
        'authorNotIn' => [
            'validatesAs'       => 'array',
            'translatesAs'      => 'author__not_in',
        ],
        'cat' => [
            'validatesAs'       => 'integer',
            'translatesAs'      => 'cat',
        ],
        'categoryName' => [
            'validatesAs'       => 'string',
            'translatesAs'      => 'category_name',
        ],
        'categoryName' => [
            'validatesAs'       => 'string',
            'translatesAs'      => 'category_name',
        ],
        'categoryAnd' => [
            'validatesAs'       => 'integer',
            'translatesAs'      => 'category__and',
        ],
        'categoryIn' => [
            'validatesAs'       => 'array',
            'translatesAs'      => 'category__in',
        ],
        'categoryNotIn' => [
            'validatesAs'       => 'array',
            'translatesAs'      => 'category__not_in',
        ],
    ];


    public function isSupported($name)
    {
        return isset($this->supportedMethods[$name]);
    }


    public function getTranslatedQueryKey($name)
    {
        return $this->supportedMethods[$name]['translatesAs'];
    }


    public function getValidationType($name)
    {
        return $this->supportedMethods[$name]['validatesAs'];
    }
}
