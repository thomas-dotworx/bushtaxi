<?php

namespace Bushtaxi;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @group sub
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

    public function testSub()
    {
        $repClient = new Server(
            [
                'service' => [
                    'name' => 'client'
                ],
                'links' => [
                    'server' => [
                        'type' => 'sub',
                        'subscriptions' => [
                            'Hello World' => ''
                        ]
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
                    printf("waiting for message\n");
                    $response = $links['server']->recv();
                    $this->running = false;
                    $this->parent->assertEquals("Hello World", $response);
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