<?php

namespace BushtaxiTest;

use PHPUnit\Framework\TestCase;
use Bushtaxi\Server;

/**
 * @group worker
 */
class WorkerTest extends TestCase
{
    public function setUp()
    {
        $this->app = new \Pimple\Container();
        $this->app->register(new LogProvider());
        $this->app['config'] = json_decode(file_get_contents(__DIR__ . '/worker.json'), true);
        $this->app['runtime'] = new class($this, $this->app['log']) extends TestRuntime {
            public function init($links) {
                $this->log->debug("Telling proxy that I'm ready");
                $links['proxy']->send("READY");
                $this->log->debug("Done");
                $this->timeout = 5;
            }
            public function handle($links) {
                $this->log->debug("Waiting for work from proxy");

                $client_address = $links['proxy']->recv();
                $this->log->debug("Client address is " . bin2hex($client_address));

                $links['proxy']->recv();
                $this->log->debug("Received empty frame");

                $message = $links['proxy']->recv();
                $this->log->debug("Received message: $message");

                $this->log->debug(sprintf("need to work for %s", bin2hex($client_address)));
                $this->log->debug("message: $message");

                $links['proxy']->send($client_address, \ZMQ::MODE_SNDMORE);
                $links['proxy']->send('', \ZMQ::MODE_SNDMORE);
                $links['proxy']->send('OK');

                $this->log->debug("sent back reply to client");
                sleep(1);
            }
        };
        $this->app->register(new BushtaxiProvider());

    }

    public function testReq()
    {
        $this->app['bushtaxi.server']->run();
    }
}
