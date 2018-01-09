<?php

namespace Sanderdekroon\Parlant\Tests;

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
}
