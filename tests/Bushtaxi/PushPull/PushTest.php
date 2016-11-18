<?php

namespace BushtaxiTest;

use PHPUnit\Framework\TestCase;
use Bushtaxi\Server;

/**
 * @group push
 */
class PushTest extends TestCase
{
    public function setUp()
    {
        $this->app = new \Pimple\Container();
        $this->app->register(new LogProvider());
        $this->app['config'] = json_decode(file_get_contents(__DIR__ . '/push.json'), true);
        $this->app['runtime'] = new class($this, $this->app['log']) extends TestRuntime {
            public function handle($links) {
                $this->log->debug("Push out Hello");
                $links['client']->send("Hello");
                $this->log->debug("Done");
                $this->running = false;
            }
        };
        $this->app->register(new BushtaxiProvider());

    }

    public function testReq()
    {
        $this->app['bushtaxi.server']->run();
    }
}
