<?php

namespace Bushtaxi;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;


/**
 * @group pub
 */
class PubTest extends TestCase
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

    public function testPub()
    {
        $reqServer = new Server(
            [
                'service' => [
                    'name' => 'server'
                ],
                'links' => [
                    'client' => [
                        'type' => 'pub',
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
                    $links['client']->send("Hello World");
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