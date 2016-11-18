<?php

namespace BushtaxiTest;

use \Pimple\Container;
use \Monolog\Handler\StreamHandler;
use \Monolog\Logger;
use \Bramus\Monolog\Formatter\ColoredLineFormatter;

class LogProvider
    implements \Pimple\ServiceProviderInterface

{
    public function register(Container $container)
    {
        $container['log'] = function($container) {
            $log = new Logger($container['config']['service']['name']);
            $handler = new StreamHandler(
                'php://stdout',
                Logger::DEBUG
            );
            $handler->setFormatter(new ColoredLineFormatter());
            $log->pushHandler($handler);
            return $log;
        };
    }
}
