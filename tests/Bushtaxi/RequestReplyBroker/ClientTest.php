<?php

namespace BushtaxiTest;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Bushtaxi\Server;


/**
 * @group client
 */
class ClientTest extends TestCase
{
    public function setUp()
    {
        $this->app = new \Pimple\Container();
        $this->app->register(new LogProvider());
        $this->app['config'] = json_decode(file_get_contents(__DIR__ . '/client.json'), true);
        $this->app['runtime'] = new class($this, $this->app['log']) extends TestRuntime {
            public function handle($links) {
                $this->log->debug("sending Hello");
                $links['broker']->send("Hello");
                $this->log->debug("done.");
                $this->log->debug("Waiting for reply");
                $reply = $links['broker']->recv();
                $this->log->debug("Reply is $reply");
                $this->phpunit->assertEquals("World", $reply);
                $this->running = false;
            }
        };
        $this->app->register(new BushtaxiProvider());
    }

    public function testPub()
    {
        $this->app['bushtaxi']->run();
    }
}