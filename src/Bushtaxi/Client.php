<?php

namespace Bushtaxi;

class Client
{
    public function __construct($config, $log = null)
    {
        $this->config = $config;
        $this->log = $log;

        if (is_null($log)) {
            $this->log = new class { function __call($a, $x) {} };
        }

        $this->linkManager = new LinkManager($config, $this->log);
        $this->linkManager->connect();
    }

    public function getLink($name)
    {
        return $this->linkManager->getLink($name);
    }

    public function __get($name)
    {
        return $this->getLink($name);
    }

}
