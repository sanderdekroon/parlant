<?php

namespace Sanderdekroon\Parlant\Tests;

use WP_Query;
use PHPUnit\Framework\TestCase;
use Sanderdekroon\Parlant\Posttype;
use Sanderdekroon\Parlant\Configurator\ParlantConfigurator;
use Sanderdekroon\Parlant\Configurator\PosttypeConfigurator;

class ConfigurationTest extends TestCase
{
    /** The return type should be configurable within a method chain */
    public function testCanConfigureReturnType()
    {
        $mock = $this->getMockBuilder('WP_Query')->getMock();

        $query = Posttype::any()->setConfig('return', 'query')->get();
        $this->assertInstanceOf('WP_Query', $query);
    }

    /** Any config value should be configurable within a method chain */
    public function testCanConfigureWithinQuery()
    {
        $query = Posttype::any()->setConfig('posts_per_page', 99)->get();

        $this->assertEquals(99, $query['posts_per_page']);
    }

    /** Every query should, by default, obey to the global config */
    public function testCanConfigureGloballyDefaultPosttype()
    {
        ParlantConfigurator::global('post_type', 'comments');
        $configurator = new ParlantConfigurator;

        $this->assertEquals('comments', $configurator->get('post_type'));
    }

    /** The global configuration should be settable with an array */
    public function testCanConfigureGloballyWithArray()
    {
        $settings = [
            'posts_per_page'    => 1337,
            'post_status'       => 'draft',
        ];
        ParlantConfigurator::global($settings);

        $query = Posttype::any()->get();
        
        $this->assertEquals(1337, $query['posts_per_page']);
        $this->assertEquals('draft', $query['post_status']);
    }

    /** The global configuration should be settable with a Configurator implementation */
    public function testCanConfigureWithConfiguratorImplementation()
    {
        // $posttypes = new Posttype;
        // $posttypes->configure(
        //     new PosttypeConfigurator([
        //         'posts_per_page'    => '7331',
        //     ])
        // );
        // $query = $posttypes->any()->get();

        // $this->assertEquals(1337, $query['posts_per_page']);
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /** Reset the global configuration before every test. */
    public function setup()
    {
        ParlantConfigurator::global([
            'posts_per_page'    => -1,
            'post_type'         => 'any',
            'post_status'       => 'publish',
            'return'            => 'argument',
        ]);
    }
}
