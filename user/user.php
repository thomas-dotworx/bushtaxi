<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = new \Pimple\Container();
$app->register(new Bushtaxi\BushtaxiProvider());

$app['config'] = json_decode(
    file_get_contents(__DIR__ . '/service.json')
);

$app['bushtaxi.server']->run();
