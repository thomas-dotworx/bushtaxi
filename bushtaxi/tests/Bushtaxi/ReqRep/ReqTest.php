<?php

namespace Bushtaxi;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @group req
 */
class ReqTest extends TestCase
{
    private function getLogger($name)
    {
        $log = new \Monolog\Logger($name);
        $log->pushHandler(
            new \Monolog\Handler\StreamHandler('php://stdout'),
            \Monolog\Logger::DEBUG
        );
        return $log;
    }

    public function testReq()
    {
        $repClient = new Server(
            [
                'service' => [
                    'name' => 'client'
                ],
                'links' => [
                    'server' => [
                        'type' => 'req',
                    ]
                ]
            ],
            $this->getLogger("client"),
            new class($this) implements \Bushtaxi\Runtime {
                private $running = true, $parent;
                public function __construct($parent) {
                    $this->parent = $parent;
                }
                public function init() {}
                public function handle($links) {
                    $links['server']->send("Hello");
                    $response = $links['server']->recv();
                    $this->running = false;
                    $this->parent->assertEquals("World", $response);
                }
                public function isRunning()
                {
                    $this->started = isset($this->started) ? $this->started : time();
                    return $this->running === true && (time() - $this->started < 10);
                }
            }
        );
        $repClient->run();
    }
}