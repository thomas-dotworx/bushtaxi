<?php

namespace BushtaxiDemo;

class UserRuntime implements \Bushtaxi\Runtime
{
    private $context, $subscriber;

    public function isRunning()
    {
        return true;
    }

    public function init()
    {
    }

    public function handle()
    {
        /*
        //$requestRaw = $this->subscriber->recv();
        //$requestRaw = $this->subscriber->recv();
        //printf ("[%s] %s", $requestRaw, PHP_EOL);
        //$request = json_decode($requestRaw);

        $response = new \ZMQSocket($this->context, \ZMQ::SOCKET_PUSH);
        printf ("send reply to %s%s", $request->sender, PHP_EOL);

        $response->connect($request->sender);
        $response->send("hello world");
        */
    }
}