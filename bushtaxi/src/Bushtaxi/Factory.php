<?php

namespace Bushtaxi;

class Factory
{
    public function factory()
    {
        $app = new \Pimple\Container();

        $app['config'] = json_decode(file_get_contents('/bushtaxi/service.json'));

        $app['log'] = function ($a) {
            $log = new \Monolog\Logger($a['config']->service->name);
            $log->pushHandler(
                new Monolog\Handler\StreamHandler('php://stdout'),
                \Monolog\Logger::DEBUG
            );
            return $log;
        };

        $app['bushtaxi.server'] = function($a) {
            return new \Bushtaxi\Server(
                $a['config'],
                $a['log'],
                $a['runtime']
            );
        };
    }
}

