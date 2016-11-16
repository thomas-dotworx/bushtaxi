<?php
namespace Bushtaxi;

use Pimple\Container;

class BushtaxiProvider
    implements \Pimple\ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['bushtaxi.server'] = function($c) {
            return new \Bushtaxi\Server(
                $c['config'],
                $c['log'],
                $c['runtime']
            );
        };

        $app['log'] = function($a) {
            $log = new \Monolog\Logger($a['config']->service->name);
            $log->pushHandler(
                new \Monolog\Handler\StreamHandler('php://stdout'),
                \Monolog\Logger::DEBUG
            );
            return $log;
        };

        $app['runtime'] = function($c) {
            return new $c['config']->service->runtime();
        };

    }
}
