<?php

namespace BushtaxiTest;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Bushtaxi\Server;

/**
 * @group req
 */
class ReqTest extends TestCase
{
    public function setUp()
    {
        $this->app = new \Pimple\Container();
        $this->app->register(new LogProvider());
        $this->app['config'] = json_decode(file_get_contents(__DIR__ . '/req.json'), true);
        $this->app->register(new BushtaxiProvider());
    }

    public function testReq()
    {
        $server = $this->app['bushtaxi.client']->getLink('server');
        $this->app['log']->debug("Sending Hello to server");
        $server->send("Hello");
        $this->app['log']->debug("Waiting for response from server");
        $response = $server->recv();
        $this->app['log']->debug("Received message $response from server");
        $this->assertEquals("World", $response);
    }
}
