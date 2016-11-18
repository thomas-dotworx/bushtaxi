<?php

require 'vendor/autoload.php';

$config = [
  "service" => [ "name" => "server" ],
  "links" => [
    "client" => [
      "type" => "rep",
      "bind" => "tcp://127.0.0.1:5000"
    ]
  ]
];

$runtime = new class extends Bushtaxi\AbstractServerRuntime {
    function handle($links) {
        $message = $links['client']->recv();
        $links['client']->send("World");
    }
};

$bushtaxi = new Bushtaxi\Server($config, $runtime);
$bushtaxi->run();

