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
class WhereMetaTest extends TestCase
{
    /** Test that a basic where statement adds the values correctly to the query. */
    public function testWhereMetaKeyIsEqualToValue()
    {
        $query = Posttype::type('post')->whereMeta('foo', '=', 'bar')->get();
        $this->assertArrayHasKey('meta_query', $query);

        $metaQuery = $query['meta_query'][0]; // Grab the first entry in the meta_query
        $this->assertArraySubset([
            'key'       => 'foo',
            'value'     => 'bar',
            'compare'   => '=',
            'type'      => 'CHAR',
        ], $metaQuery);
    }


    /** Test that a basic where statement adds the values correctly to the query. */
    public function testWhereMetaCanOmitOperator()
    {
        $query = Posttype::type('post')->whereMeta('foo', 'bar')->get();
        $this->assertArrayHasKey('meta_query', $query);

        $metaQuery = $query['meta_query'][0]; // Grab the first entry in the meta_query
        $this->assertArraySubset([
            'key'       => 'foo',
            'value'     => 'bar',
            'compare'   => '=',
            'type'      => 'CHAR',
        ], $metaQuery);
    }

    /** Test the negating operator for a whereMeta clause. */
    public function testWhereMetaWithNegatingOperator()
    {
        $query = Posttype::type('post')->whereMeta('password', '!=', 'hunter2')->get();
        $metaQuery = $query['meta_query'][0]; // Grab the first entry in the meta_query

        $this->assertArraySubset([
            'key'       => 'password',
            'value'     => 'hunter2',
            'compare'   => '!=',
            'type'      => 'CHAR',
        ], $metaQuery);
    }

    /** Test that an invalid operator will trigger an exception. */
    public function testWhereMetaWithInvalidOperatorThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);

        $query = Posttype::type('post')->whereMeta('password', 'invalid', 'hunter2')->get();
    }

    /** Test that type comparisons for the meta_query can be set */
    public function testWhereMetaWithTypeComparison()
    {
        $query = Posttype::type('post')->whereMeta('meaning_of_life', '=', 42, 'NUMERIC')->get();
        $metaQuery = $query['meta_query'][0]; // Grab the first entry in the meta_query

        $this->assertArraySubset([
            'key'       => 'meaning_of_life',
            'value'     => 42,
            'compare'   => '=',
            'type'      => 'NUMERIC',
        ], $metaQuery);
    }

    /** Test that invalid type comparisons fallback to the CHAR type */
    public function testWhereMetaWithInvalidTypeFallbackToChar()
    {
        $query = Posttype::type('post')->whereMeta('meaning_of_life', '=', 42, 'INVALID_TYPE')->get();
        $metaQuery = $query['meta_query'][0]; // Grab the first entry in the meta_query

        $this->assertArraySubset([
            'key'       => 'meaning_of_life',
            'value'     => 42,
            'compare'   => '=',
            'type'      => 'CHAR',
        ], $metaQuery);
    }

    /** Test a whereMeta clause where the relation between the items is OR */
    /** Only tests the OR relation, since AND is the default */
    public function testWhereMetaWithOrRelation()
    {
        $query = Posttype::type('post')
            ->whereMeta('meaning_of_life', '=', 42, 'NUMERIC')
            ->orWhereMeta('bar', 'baz')
            ->get();

        $metaQuery = $query['meta_query'];
        
        $this->assertCount(3, $metaQuery);
        $this->assertArrayHasKey('relation', $metaQuery);
        $this->assertTrue($metaQuery['relation'] === 'OR');
    }

    /** Test that nesting whereMetas with a close resolve correctly. */
    public function testNestedWhereMetaQuery()
    {
        $query = Posttype::any()
            ->whereMeta(function () {
                return $this->where('size', 'M')->orWhere('size', 'L');
            })
            ->whereMeta('color', 'red')
            ->get();

        $metaQuery = $query['meta_query'];

        $this->assertArraySubset([
            [
                [
                    'key'       => 'size',
                    'value'     => 'M',
                    'compare'   => '=',
                    'type'      => 'CHAR',
                ], [
                    'key'       => 'size',
                    'value'     => 'L',
                    'compare'   => '=',
                    'type'      => 'CHAR',
                ],
                'relation'  => 'OR',
            ], [
                'key'       => 'color',
                'value'     => 'red',
                'compare'   => '=',
                'type'      => 'CHAR',
            ],
        ], $metaQuery);
    }
}
