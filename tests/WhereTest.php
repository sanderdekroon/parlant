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
class WhereTest extends TestCase
{
    /** Test that a basic where statement adds the values correctly to the query. */
    public function testWhereKeyIsEqualToValue()
    {
        $query = Posttype::any()->where('foo', '=', 'bar')->get();
        $this->assertArrayHasKey('foo', $query);
        $this->assertTrue($query['foo'] == 'bar');
    }

    /** Test the where shorthand where the equals sign is omitted. */
    public function testWhereKeyIsEqualToValueShorthand()
    {
        $query = Posttype::any()->where('bar', 'baz')->get();
        $this->assertArrayHasKey('bar', $query);
        $this->assertTrue($query['bar'] == 'baz');
    }

     /** Test that supplying a invalid operator will cause an exception. */
    public function testWhereWithInvalidOperator()
    {
        $this->expectException(InvalidArgumentException::class);

        $query = Posttype::any()->where('foo', 'bar', 'baz')->get();
    }

    /** Test passing an array of where statements to the where() method */
    public function testWhereWithArrayOfWheres()
    {
        $this->markTestIncomplete('This test and method has not been implemented yet.');

        $query = Posttype::any()->where([
            ['foo', 'bar'],
            ['meaning', 'of life'],
            ['universe', 42],
        ])->get();

        $this->assertArrayHasKey('foo', $query);
        $this->assertArrayHasKey('meaning', $query);
        $this->assertArrayHasKey('universe', $query);

        $this->assertTrue($query['foo'] == 'bar');
        $this->assertTrue($query['meaning'] == 'of life');
        $this->assertTrue($query['universe'] == 42);
    }


    public function testWhereMethodOperatorActuallyWorks()
    {
        $this->markTestIncomplete('This test and method has not been implemented yet. Note to self: figure out support for this.');
        // author__not_in
        // category__not_in
        // tag__not_in
        // post_parent__not_in
        // post__not_in

        // author__in
        // category__in
        // tag__in
        // tag_slug__in
        // post_parent__in
        // post__in

        // category__and
        // tag__and
        // tag_slug__and
    }
}
