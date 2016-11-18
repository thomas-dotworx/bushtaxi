<?php

namespace BushtaxiTest;

abstract class TestRuntime implements \Bushtaxi\ServerRuntime
{
    protected $phpunit, $log, $running = true, $start, $timeout;

    public function __construct($phpunit, $log)
    {
        $this->phpunit = $phpunit;
        $this->log = $log;
        $this->start = time();
    }

    public function init($links)
    {

    }

    public function isRunning()
    {

        if (!is_null($this->timeout)) {
            if (time() - $this->start > $this->timeout) {
                $this->log->debug("reached timeout of {$this->timeout} seconds");
                return false;
            }
        }

        return $this->running === true;
    }
    public function shutdown()
    {
    }
}
