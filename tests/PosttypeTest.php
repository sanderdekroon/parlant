<?php

namespace Sanderdekroon\Parlant\Tests;

use BadMethodCallException;
use PHPUnit\Framework\TestCase;
use Sanderdekroon\Parlant\Posttype;
use Sanderdekroon\Parlant\Builder\PosttypeBuilder;
use Sanderdekroon\Parlant\Configurator\ParlantConfigurator;

/**
 * Test the Posttype class
 */
class PosttypeTest extends TestCase
{
    /** Reset the global configuration to the default of our test suite before running these tests */
    public static function setUpBeforeClass()
    {
        ParlantConfigurator::globally([
            'posts_per_page'    => -1,
            'post_type'         => 'any',
            'post_status'       => 'publish',
            'return'            => 'argument',
        ]);
    }

    /** The any method should return an instance of the PosttypeBuilder */
    public function testAnyMethodReturnsBuilder()
    {
        $query = Posttype::any();
        $this->assertInstanceOf(PosttypeBuilder::class, $query);
    }

    /** The type method should return an instance of the PosttypeBuilder */
    public function testTypeMethodReturnsBuilder()
    {
        $query = Posttype::type('posttype');
        $this->assertInstanceOf(PosttypeBuilder::class, $query);
    }

    /** The any() method should return all available posts */
    public function testAnyMethodReturnsAllPosts()
    {
        $arguments = Posttype::any()->get();

        $this->assertArrayHasKey('posts_per_page', $arguments);
        $this->assertEquals(-1, $arguments['posts_per_page']);
    }

    /** The type method should set the post_type argument correctly */
    public function testTypeSetsPosttype()
    {
        $query = Posttype::type('something')->get();

        $this->assertArrayHasKey('post_type', $query);
        $this->assertEquals('something', $query['post_type']);
    }

    /** The find() method should return one post by it's ID */
    public function testFindMethod()
    {
        $query = Posttype::find(42);

        $this->assertArrayHasKey('p', $query);
        $this->assertEquals(42, $query['p']);
    }


    public function testLimitMethod()
    {
        $query = Posttype::any()->limit(5)->get();

        $this->assertArrayHasKey('posts_per_page', $query);
        $this->assertEquals(5, $query['posts_per_page']);
    }


    public function testOffsetMethod()
    {
        $query = Posttype::any()->offset(10)->get();

        $this->assertArrayHasKey('offset', $query);
        $this->assertEquals(10, $query['offset']);
    }

    /** Test the all() method on the end of a builder chain */
    public function testAllMethodOnBuilderReturnsAllPosts()
    {
        $query = Posttype::type('posttype')->all();

        $this->assertArrayHasKey('posts_per_page', $query);
        $this->assertEquals(-1, $query['posts_per_page']);
    }

    /** Test the static all() shortcut method */
    public function testStaticAllMethodReturnsAllPosts()
    {
        $query = Posttype::all('posttype');

        $this->assertArrayHasKey('posts_per_page', $query);
        $this->assertEquals(-1, $query['posts_per_page']);
    }

    /** Test the static all() shortcut method */
    public function testStaticAllMethodSetsPosttype()
    {
        $query = Posttype::all('posttype');

        $this->assertArrayHasKey('post_type', $query);
        $this->assertEquals('posttype', $query['post_type']);
    }


    public function testAvgMethod()
    {
        $this->expectException(BadMethodCallException::class);

        $query = Posttype::type('post')->avg();
    }

    public function testMaxMethod()
    {
        $this->expectException(BadMethodCallException::class);

        $query = Posttype::type('post')->max();
    }

    public function testMinMethod()
    {
        $this->expectException(BadMethodCallException::class);

        $query = Posttype::type('post')->min();
    }

    public function testOrderMethod()
    {
        $query = Posttype::type('posttype')->order('ASC')->all();

        $this->assertArrayHasKey('order', $query);
        $this->assertEquals('ASC', $query['order']);
    }

    public function testOrderByMethod()
    {
        $query = Posttype::type('posttype')->orderBy('title')->all();

        $this->assertArrayHasKey('orderby', $query);
        $this->assertEquals('title', $query['orderby']);
    }

    public function testOrderByMethodWithDirection()
    {
        $query = Posttype::type('posttype')->orderBy('author', 'DESC')->all();

        $this->assertArrayHasKey('orderby', $query);
        $this->assertEquals('author', $query['orderby']);

        $this->assertArrayHasKey('order', $query);
        $this->assertEquals('DESC', $query['order']);
    }
}
