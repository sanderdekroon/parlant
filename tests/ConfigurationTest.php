<?php

namespace Sanderdekroon\Parlant\Tests;

use WP_Query;
use PHPUnit\Framework\TestCase;
use Sanderdekroon\Parlant\Posttype;
use Sanderdekroon\Parlant\Configurator\ParlantConfigurator;

class ConfigurationTest extends TestCase
{

    public function testCanConfigureReturnType()
    {
        $mock = $this->getMockBuilder('WP_Query')->getMock();

        $query = Posttype::any()->setConfig('return', 'query')->get();
        $this->assertInstanceOf('WP_Query', $query);
    }

    
    public function testCanConfigureWithinQuery()
    {
        $query = Posttype::any()->setConfig('posts_per_page', 99)->get();

        $this->assertEquals(99, $query['posts_per_page']);
    }

    
    public function testCanConfigureDefaultPosttype()
    {
        ParlantConfigurator::global('post_type', 'comments');
        $configurator = new ParlantConfigurator;

        $this->assertEquals('comments', $configurator->get('post_type'));
    }

    
    // public function testGlobalSettingsCanBeReset()
    // {
    //     ParlantConfigurator::reset();

    //     $this->assertNotEquals('comments', $configurator->get('post_type'));
    // }

    
    public function testCanConfigureWithArray()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    
    public function testCanConfigureWithConfiguratorImplementation()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    
    public function testCanSetConfigurationInQuery()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    
    public function testAppliesDefaultConfiguration()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }
}
