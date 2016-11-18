<?php

namespace BushtaxiTest;

use Bushtaxi\Server;
use Bushtaxi\Client;
use \Pimple\Container;

class BushtaxiProvider
    implements \Pimple\ServiceProviderInterface

{
    public function register(Container $container)
    {
        $container['bushtaxi.server'] = function($container) {
            return new Server(
                $container['config'],
                $container['runtime'],
                $container['log']
            );
        };

        $container['bushtaxi.client'] = function($container) {
            return new Client(
                $container['config'],
                $container['log']
            );
        };
    }
}
