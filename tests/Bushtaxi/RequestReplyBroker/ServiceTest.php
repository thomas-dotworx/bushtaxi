<?php

namespace BushtaxiTest;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Bushtaxi\Server;

/**
 * @group service
 */
class ServiceTest extends TestCase
{

    public function setUp()
    {
        $this->app = new \Pimple\Container();
        $this->app->register(new LogProvider());
        $this->app['config'] = json_decode(file_get_contents(__DIR__ . '/service.json'), true);
        $this->app['runtime'] = new class($this, $this->app['log']) extends TestRuntime {
            public function handle($links) {
                $this->log->debug("Waiting for message");
                $message = $links['broker']->recv();
                $this->log->debug("Received message $message. Send Reply");
                $links['broker']->send("World");
                $this->running = false;
            }
        };
        $this->app->register(new BushtaxiProvider());
    }

    public function testSub()
    {
        $this->app['bushtaxi']->run();
    }
}