<?php

namespace BushtaxiDemo;

use Bushtaxi\Runtime;

class DispatcherRuntime implements Runtime
{
    private $router, $responder, $dispatcher;

    public function init()
    {
        $context = new \ZMQContext(2);

        $this->router = \FastRoute\simpleDispatcher(function(\FastRoute\RouteCollector $r) {
            $r->addRoute('GET', '/users', 'dispatcher.user.index');
            $r->addRoute('GET', '/user/{id:\d+}', 'dispatcher.user.get');
        });

        //  Socket to talk to clients
        $this->responder = new \ZMQSocket($context, \ZMQ::SOCKET_PULL);
        $this->responder->bind(getenv('BIND_ADDRESS'));
        printf ("Listening on %s%s", getenv('BIND_ADDRESS'), PHP_EOL);

        $this->dispatcher = new \ZMQSocket($context, \ZMQ::SOCKET_PUB);
        $this->dispatcher->bind("tcp://*:5563");
    }

    public function isRunning()
    {
        return true;
    }

    public function handle()
    {
        $requestRaw = $this->responder->recv();
        printf ("Received request: [%s]%s", $requestRaw, PHP_EOL);
        $request = json_decode($requestRaw);
        $routeInfo = $this->router->dispatch('GET', $request->request_uri);


        if (!isset($request->sender)) {
            return;
        }

        /*
        printf ("Responding to %s%s", $request->sender, PHP_EOL);
        $sock = new ZMQSocket($context, ZMQ::SOCKET_PUSH);
        $sock->connect($request->sender);
        $sock->send("jo");
        */
        printf ("Firing event [%s]%s", $routeInfo[1], PHP_EOL);
        $request->route_info = $routeInfo;
        $this->dispatcher->send($routeInfo[1], \ZMQ::MODE_SNDMORE);
        $this->dispatcher->send(json_encode($request));
        //$responder->send(json_encode($routeInfo));
    }
}
