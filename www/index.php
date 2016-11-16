<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = new \Pimple\Container();
$app->register(new Bushtaxi\BushtaxiProvider());

$app['config'] = json_decode(
    file_get_contents(__DIR__ . '/service.json')
);

$app['bushtaxi.server']->run();

header('Content-Type: application/json');

/*
$localIP = getHostByName(getHostName());
$bindAddr = sprintf("tcp://%s:%d", $localIP, 5564);

$context = new ZMQContext(1);
$requester = new ZMQSocket($context, ZMQ::SOCKET_REQ);
file_put_contents(STDOUT, sprintf('Connecting to %d%s', getenv('DISPATCHER'), PHP_EOL));
$requester->connect(getenv('DISPATCHER'));
$requester->send(
    json_encode(
        [
        "request_uri" => $_SERVER['REQUEST_URI'],
        "sender" => $bindAddr
        ]
    )
);

$responder = new ZMQSocket($context, ZMQ::SOCKET_PULL);
$receiver = $responder->bind($bindAddr);

$read = $write = [];

$poller = new ZMQPoll();
$poller->add($receiver, ZMQ::POLL_IN);
$resp = $poller->poll($read, $write, 100);

if ($resp) {
    var_dump($read[0]->recv());
} else {
    echo "timed out";
}

/*
try {
    $dispatch2 = new ZMQSocket($context, ZMQ::SOCKET_PUB);
    $dispatch2->bind("tcp://*:5563");
} catch (ZMQSocketException $e) {
    print_r($e);
}
*/

//print_r($responder->recv());


