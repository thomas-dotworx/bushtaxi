<?php

namespace Bushtaxi;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;


/**
 * @group rep
 */
class RepTest extends TestCase
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

    public function testRep()
    {
        $reqServer = new Server(
            [
                'service' => [
                    'name' => 'server'
                ],
                'links' => [
                    'client' => [
                        'type' => 'rep',
                    ]
                ]
            ],
            $this->getLogger("server"),
            new class($this) implements \Bushtaxi\Runtime {
                private $running = true, $parent;
                public function __construct($parent) {
                    $this->parent = $parent;
                }
                public function init() { }
                public function handle($links) {
                    $request = $links['client']->recv();
                    $links['client']->send("World");
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