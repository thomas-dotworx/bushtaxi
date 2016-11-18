<?php

namespace BushtaxiTest;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Bushtaxi\Server;

/**
 * @group subscriber
 */
class SubscriberTest extends TestCase
{

    public function setUp()
    {
        $this->app = new \Pimple\Container();
        $this->app->register(new LogProvider());
        $this->app['config'] = json_decode(file_get_contents(__DIR__ . '/subscriber.json'), true);
        $this->app->register(new BushtaxiProvider());
    }

    public function testSub()
    {
        $this->app['log']->debug("waiting for message");
        $proxy = $this->app['bushtaxi.client']->getLink('proxy');
        $response = $proxy->recv();
        $this->app['log']->debug("received message $response");
        $this->assertEquals("Hello World", $response);
    }
}
