<?php

namespace BushtaxiTest;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Bushtaxi\Server;


/**
 * @group proxy
 */
class ProxyTest extends TestCase
{
    public function setUp()
    {
        $this->app = new \Pimple\Container();
        $this->app->register(new LogProvider());
        $this->app['config'] = json_decode(file_get_contents(__DIR__ . '/proxy.json'), true);
        $this->app['runtime'] = new class($this, $this->app['log']) extends TestRuntime {
            public function handle($links) {
                $this->log->debug("create new device");
                $device = new \ZMQDevice(
                    $links['subscriber'],
                    $links['publisher']
                );
                $this->log->debug("run");
                $device->setTimerCallback(
                    function() { },
                    5000
                );
                $device->run();
                $this->log->debug("done");
                $this->running = false;
            }
        };
        $this->app->register(new BushtaxiProvider());
    }

    public function testPub()
    {
        $this->app['bushtaxi.server']->run();
    }
}
