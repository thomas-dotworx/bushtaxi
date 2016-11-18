<?php

namespace BushtaxiTest;

use PHPUnit\Framework\TestCase;
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
                $this->log->debug("send hello");
                $start = microtime(true);
                $links['proxy']->send("hello");
                $this->log->debug("waiting for reply");
                $reply = $links['proxy']->recv();
                $end = microtime(true);
                $this->log->debug(sprintf("got reply %s after %f seconds", $reply, ($end - $start)));

                $this->phpunit->assertEquals("OK", $reply);

                $this->running = false;
            }
        };
        $this->app->register(new BushtaxiProvider());
    }
    public function testPull()
    {
        $this->app['bushtaxi.server']->run();
    }
}
