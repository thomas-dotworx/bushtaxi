<?php

require 'vendor/autoload.php';

$config = [
  "service" => [ "name" => "server" ],
  "links" => [
    "server" => [
      "type" => "req",
      "connect" => "tcp://127.0.0.1:5000"
    ]
  ]
];

$bushtaxi = new Bushtaxi\Client($config);
$bushtaxi->server->send('Hello');
print($bushtaxi->server->recv());

