<?php

namespace BushtaxiTest;

use PHPUnit\Framework\TestCase;
use Bushtaxi\Server;


/**
 * @group pull
 */
class PullTest extends TestCase
{

    public function setUp()
    {
        $this->app = new \Pimple\Container();
        $this->app->register(new LogProvider());
        $this->app['config'] = json_decode(file_get_contents(__DIR__ . '/pull.json'), true);
        $this->app['runtime'] = new class($this, $this->app['log']) extends TestRuntime {
            public function handle($links) {
            }
        };
        $this->app->register(new BushtaxiProvider());
    }

    public function testPull()
    {
        $this->app['log']->debug("waiting for message");
        $request = $this->app['bushtaxi.client']->getLink('server')->recv();
        $this->assertEquals("Hello", $request);
    }
}
