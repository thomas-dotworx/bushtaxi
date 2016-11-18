<?php

namespace BushtaxiTest;

use PHPUnit\Framework\TestCase;
use Bushtaxi\Server;

/**
 * @group proxy
 */
class ProxyTest extends TestCase
{
    public function setUp()
    {
        $this->app = new \Pimple\Container();
        $this->app->register(new LogProvider());
        $this->app['config'] = json_decode(file_get_contents(__DIR__ . '/proxy.json'), true);
        $this->app['runtime'] = new class($this, $this->app['log']) extends TestRuntime {
            public function init($links)
            {
                $this->workers = [];
                $this->timeout = 5;
            }
            private function handle_worker($links)
            {
                $this->log->debug("handle worker");

                $worker_addr = $links['worker']->recv();
                $this->log->debug("received address from worker: " . bin2hex($worker_addr));
                $links['worker']->recv();
                $this->log->debug("received empty frame from worker");
                $message = $links['worker']->recv();
                $this->log->debug("received message from worker");
                $this->workers[] = $worker_addr;

                if ($message == "READY") {
                    $this->log->debug(sprintf("worker with address %s is ready", bin2hex($worker_addr)));
                } else {
                    $client_addr = $message;
                    $links['worker']->recv(); //empty frame
                    $message = $links['worker']->recv();
                    $this->log->debug("sending back message to client " . bin2hex($client_addr));
                    $links['client']->send($client_addr, \ZMQ::MODE_SNDMORE);
                    $links['client']->send('', \ZMQ::MODE_SNDMORE);
                    $links['client']->send($message);
                    $this->log->debug("done");



                }

            }

            private function handle_client($links) {
                $this->log->debug("handle client");

                list($client_addr, $_, $message) = [
                    $links['client']->recv(),
                    $links['client']->recv(),
                    $links['client']->recv()
                ];
                $this->log->debug(sprintf("client address is %s", bin2hex($client_addr)));
                $this->log->debug(sprintf("message is %s", $message));

                $worker_addr = array_shift($this->workers);

                $this->log->debug("passing on message to worker " . bin2hex($worker_addr));

                $links['worker']->send(
                    $worker_addr,
                    \ZMQ::MODE_SNDMORE
                );
                $links['worker']->send("", \ZMQ::MODE_SNDMORE);
                $links['worker']->send($client_addr, \ZMQ::MODE_SNDMORE);
                $links['worker']->send("", \ZMQ::MODE_SNDMORE);
                $links['worker']->send($message);

                $this->log->debug("done");
            }

            public function handle($links) {

                $readable = $writeable = [];

                $poll = new \ZMQPoll();
                if (count($this->workers) > 0) {
                    $this->log->info("Polling client");
                    $poll->add($links['client'], \ZMQ::POLL_IN);
                } else {
                    $this->log->info("There are no workers available right now.");
                }

                $poll->add($links['worker'], \ZMQ::POLL_IN);

                $this->log->debug("polling");
                $events = $poll->poll($readable, $writeable);
                $this->log->debug("done polling");

                if ($events > 0) {
                    $this->log->debug("an event has happened!");
                    foreach ($readable as $socket) {
                        if ($socket === $links['worker']) {
                            $this->handle_worker($links);
                        } elseif ($socket === $links['client']) {
                            $this->handle_client($links);
                        }
                    }
                }
            }
        };
        $this->app->register(new BushtaxiProvider());

    }

    public function testReq()
    {
        $this->app['bushtaxi.server']->run();
    }
}
