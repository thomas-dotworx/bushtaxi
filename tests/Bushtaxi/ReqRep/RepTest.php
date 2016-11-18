<?php

namespace BushtaxiTest;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Bushtaxi\Server;


/**
 * @group rep
 */
class RepTest extends TestCase
{
    public function setUp()
    {
        $this->app = new \Pimple\Container();
        $this->app->register(new LogProvider());
        $this->app['config'] = json_decode(file_get_contents(__DIR__ . '/rep.json'), true);
        $this->app['runtime'] = new class($this, $this->app['log']) extends \BushtaxiTest\TestRuntime
        {
            public function handle($links)
            {
                $this->log->debug("Waiting for message from client");
                $request = $links['client']->recv();
                $this->log->debug("Received message $request from client");
                $this->log->debug("Sending response World to client");
                $links['client']->send("World");
                $this->phpunit->assertEquals("Hello", $request);
                $this->running = false;
            }
        };
        $this->app->register(new BushtaxiProvider());
    }

    public function testRep()
    {
        $this->app['bushtaxi.server']->run();
    }
}
