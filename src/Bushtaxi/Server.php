<?php

namespace Bushtaxi;

class Server
{
    private $links = [];

    public function __construct($config, $runtime, $log = null)
    {
        $this->config = $config;
        $this->runtime = $runtime;
        $this->log = $log;

        if (is_null($log)) {
            $this->log = new class {
                public function __call($a, $b) {}
            };
        }

        $this->linkManager = new LinkManager($config, $this->log);
    }

    public function run()
    {
        $this->init();
        $this->log->info("Bushtaxi for {$this->config['service']['name']} is ready");
        while ($this->runtime->isRunning()) {
            $this->runtime->handle($this->linkManager->getLinks());
        }
        $this->runtime->shutdown();
        $this->log->debug("exit");
    }

    private function init()
    {
        $this->log->debug("init()");
        $this->linkManager->connect();

        $this->log->debug("call runtime init()");
        $this->runtime->init($this->linkManager->getLinks());

        $this->log->debug("done: init()");
    }


}
