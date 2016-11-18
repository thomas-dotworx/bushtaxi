<?php

namespace BushtaxiTest;

use PHPUnit\Framework\TestCase;
use Bushtaxi\Server;

/**
 * @group sink
 */
class SinkTest extends TestCase
{
    public function setUp()
    {
        $this->app = new \Pimple\Container();
        $this->app->register(new LogProvider());
        $this->app['config'] = json_decode(file_get_contents(__DIR__ . '/sink.json'), true);
        $this->app['runtime'] = new class($this, $this->app['log']) extends TestRuntime {
            public function init($links)
            {
                $this->stats = [];
            }
            public function handle($links) {
                $this->log->debug("Waiting for message from worker");
                $message = $links['worker']->recv();
                $this->log->debug("received this message from worker: $message");

                list($sender, $message) = explode(':', $message);

                if (!isset($this->stats[$sender])) {
                    $this->stats[$sender] = 0;
                }
                $this->stats[$sender]++;
                $this->receivedMessages++;

                $this->log->debug(
                    sprintf(
                        "This is message number %d",
                        $this->receivedMessages
                    )
                );
                $this->running = $this->receivedMessages < 1000;
            }

            public function shutdown()
            {
                $this->log->debug(print_r($this->stats, true));
                /*
                $this->phpunit->assertEquals(
                    10,
                    $this->receivedMessages,
                    "Should have received 10 messages after 30 seconds"
                );
                */
            }
        };
        $this->app->register(new BushtaxiProvider());
    }

    public function testReq()
    {
        $this->app['bushtaxi.server']->run();
    }
}
