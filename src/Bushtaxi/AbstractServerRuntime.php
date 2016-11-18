<?php

namespace Bushtaxi;

abstract class AbstractServerRuntime implements ServerRuntime
{
    public function init($links) {
    }

    public function isRunning() {
        return true;
    }

    public function handle($links) {
    }

    public function shutdown() {
    }
}
