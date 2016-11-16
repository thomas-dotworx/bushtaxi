<?php

namespace Bushtaxi;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @group push
 */
class PushTest extends TestCase
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
                    'name' => 'server'
                ],
                'links' => [
                    'client' => [
                        'type' => 'push',
                    ]
                ]
            ],
            $log = $this->getLogger("server"),
            new class($this, $log) implements \Bushtaxi\Runtime {
                private $running = true, $parent;
                public function __construct($parent, $log) {
                    $this->parent = $parent;
                    $this->log = $log;
                }
                public function init() {}
                public function handle($links) {
                    $this->log->debug("Push out Hello");
                    $links['client']->send("Hello");
                    $this->log->debug("Done");
                    $this->running = false;
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