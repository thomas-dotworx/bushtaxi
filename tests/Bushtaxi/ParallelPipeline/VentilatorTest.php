<?php

namespace BushtaxiTest;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Bushtaxi\Server;


/**
 * @group ventilator
 */
class VentilatorTest extends TestCase
{
    public function setUp()
    {
        $this->app = new \Pimple\Container();
        $this->app->register(new LogProvider());
        $this->app['config'] = json_decode(file_get_contents(__DIR__ . '/ventilator.json'), true);
        $this->app['runtime'] = new class($this, $this->app['log']) extends TestRuntime {
            public function handle($links) {
                for ($i = 0; $i < 1000; $i++) {
                    $message = "Hello Worker {$i}";
                    $this->log->debug("? sending message $message to worker");
                    $links['worker']->send($message);
                }
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
