<?php

namespace BushtaxiTest;

use PHPUnit\Framework\TestCase;


/**
 * @group publisher
 */
class PublisherTest extends TestCase
{
    public function setUp()
    {
        $this->app = new \Pimple\Container();
        $this->app->register(new LogProvider());
        $this->app['config'] = json_decode(file_get_contents(__DIR__ . '/publisher.json'), true);
        $this->app['runtime'] = new class($this, $this->app['log']) extends TestRuntime {
            public function init($links) {
                $this->timeout = 5;
            }
            public function handle($links) {
                $this->log->debug("Publish Hello World");
                $links['proxy']->send("Hello World");
                sleep(1);
            }
        };
        $this->app->register(new BushtaxiProvider());
    }

    public function testPub()
    {
        $this->app['bushtaxi.server']->run();
    }
}
