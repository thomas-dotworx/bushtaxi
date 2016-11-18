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
            public function init($link) {
                $this->id = substr(md5(uniqid()), 0, 7);
                $this->timeout = 10;
            }
            public function handle($links) {
                if ($message = $links['ventilator']->recv(\ZMQ::MODE_DONTWAIT)) {
                    $this->log->debug("Got message from Ventilator: $message");
                    $this->log->debug("Pushing message down to sink");
                    $links['sink']->send("{$this->id}:{$message}");
                    $this->log->debug("Done");
                }
                sleep(0.1);
            }
        };
        $this->app->register(new BushtaxiProvider());
    }

    public function testReq()
    {
        $this->app['bushtaxi.server']->run();
    }
}
