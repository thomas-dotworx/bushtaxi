<?php

namespace Bushtaxi;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;


/**
 * @group pull
 */
class PullTest extends TestCase
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

    public function testPull()
    {
        $reqServer = new Server(
            [
                'service' => [
                    'name' => 'client'
                ],
                'links' => [
                    'server' => [
                        'type' => 'pull',
                    ]
                ]
            ],
            $log = $this->getLogger("client"),
            new class($this, $log) implements \Bushtaxi\Runtime {
                private $running = true, $parent;
                public function __construct($parent, $log) {
                    $this->parent = $parent;
                    $this->log = $log;
                }
                public function init() { }
                public function handle($links) {
                    $this->log->debug("waiting for message");
                    $request = $links['server']->recv();
                    $this->parent->assertEquals("Hello", $request);
                    $this->running = false;
                }
                public function isRunning()
                {
                    $this->started = isset($this->started) ? $this->started : time();
                    return $this->running === true && (time() - $this->started < 10);
                }
            }
        );



        $reqServer->run();
    }
}