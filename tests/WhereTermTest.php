<?php

namespace Sanderdekroon\Parlant\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Sanderdekroon\Parlant\Posttype;
use Sanderdekroon\Parlant\Builder\PosttypeBuilder;
use Sanderdekroon\Parlant\Configurator\ParlantConfigurator;

/**
 * Test querying posts by using the where() method.
 */
class WhereTermTest extends TestCase
{

    //Namechange van whereTaxonomy naar whereTerm!!
    



    /** Test that a basic where statement adds the values correctly to the query. */
    public function testWhereTermInTaxonomyIsEqualToValue()
    {
        $query = Posttype::type('jeans')->whereTaxonomy('size', 'name', 'IN', 37)->get();
        $this->assertArrayHasKey('tax_query', $query);

        $termQuery = $query['tax_query'][0]; // Grab the first entry in the meta_query
        $this->assertArraySubset([
            'taxonomy'          => 'size',
            'field'             => 'name',
            'terms'             => 37,
            'include_children'  => true,
            'operator'          => 'IN',
        ], $termQuery);
    }


    public function testWhereTaxonomyCanOmitOperator()
    {
        $query = Posttype::type('jeans')->whereTaxonomy('size', 'name', 37)->get();

        $termQuery = $query['tax_query'][0];
        $this->assertArraySubset([
            'taxonomy'          => 'size',
            'field'             => 'name',
            'terms'             => 37,
            'include_children'  => true,
            'operator'          => 'IN',
        ], $termQuery);
    }


    public function testWhereTaxonomyWithOrRelation()
    {
        $query = Posttype::type('jeans')
            ->whereTaxonomy('size', 'term_name', '32')
            ->orWhereTaxonomy('size', 'term_name', '33')
            ->get();

        $termQuery = $query['tax_query'];
        $this->assertCount(3, $termQuery);
        $this->assertArrayHasKey('relation', $termQuery);
        $this->assertTrue($termQuery['relation'] === 'OR');
    }

    
    public function testNestedWhereTaxonomyQuery()
    {
        $query = Posttype::type('jeans')
            ->whereTaxonomy(function () {
                return $this->relation('OR')
                    ->slug('category', 'news_articles')
                    ->name('size', '32')
                    ->termTaxonomyId('size', 34)
                    ->id('size', 3);
            })
            ->whereTaxonomy('color', 'slug', 'blue')
            ->get();

        $this->assertArraySubset([
            [
                [
                    'taxonomy'          => 'category',
                    'field'             => 'slug',
                    'terms'             => 'news_articles',
                    'include_children'  => true,
                    'operator'          => 'IN',
                ], [
                    'taxonomy'          => 'size',
                    'field'             => 'name',
                    'terms'             => '32',
                    'include_children'  => true,
                    'operator'          => 'IN',
                ], [
                    'taxonomy'          => 'size',
                    'field'             => 'term_taxonomy_id',
                    'terms'             => 34,
                    'include_children'  => true,
                    'operator'          => 'IN',
                ], [
                    'taxonomy'          => 'size',
                    'field'             => 'term_id',
                    'terms'             => 3,
                    'include_children'  => true,
                    'operator'          => 'IN',
                ],
                'relation'          => 'OR',
            ], [
                'taxonomy'          => 'color',
                'field'             => 'slug',
                'terms'             => 'blue',
                'include_children'  => true,
                'operator'          => 'IN',
            ],
        ], $query['tax_query']);
    }

// Post::type('jeans')
//     ->whereTaxonomy('size', function () {
//         return $this->relation('OR')->name('32')->name('33')->name('34')->name('35');
//     })->get();



    // Post::type('jeans')
    // ->whereTaxonomy('size', function () {
    //     return $this->relation('OR')->name('32')->name('33')->name('34')->name('35');
    // })->get();
}
