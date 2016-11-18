<?php

namespace BushtaxiTest;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Bushtaxi\Server;

/**
 * @group sub
 */
class SubTest extends TestCase
{

    public function setUp()
    {
        $this->app = new \Pimple\Container();
        $this->app->register(new LogProvider());
        $this->app['config'] = json_decode(file_get_contents(__DIR__ . '/sub.json'), true);
        $this->app['runtime'] = new class($this, $this->app['log']) extends TestRuntime {
            public function handle($links) {
                $this->log->debug("waiting for message");
                $response = $links['server']->recv();
                $this->running = false;
                $this->phpunit->assertEquals("Hello World", $response);
            }
        };
        $this->app->register(new BushtaxiProvider());
    }

    public function testSub()
    {
        $this->app['bushtaxi.server']->run();
    }
}
