<?php

namespace BushtaxiTest;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Bushtaxi\Server;

/**
 * @group pub
 */
class PubTest extends TestCase
{
    public function setUp()
    {
        $this->app = new \Pimple\Container();
        $this->app->register(new LogProvider());
        $this->app['config'] = json_decode(file_get_contents(__DIR__ . '/pub.json'), true);
        $this->app['runtime'] = new class($this, $this->app['log']) extends TestRuntime {
            public function init($links) {
                $this->timeout = 10;
            }
            public function handle($links) {
                $links['client']->send("Hello World");
            }
        };
        $this->app->register(new BushtaxiProvider());
    }

    public function testPub()
    {
        $this->app['bushtaxi.server']->run();
    }
}
