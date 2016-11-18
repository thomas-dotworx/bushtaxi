<?php

namespace Bushtaxi;

interface ServerRuntime
{
    /**
     * Called when the server starts up.
     */
    function init($links);

    /**
     * Determines wheter the server is running or not.
     */
    function isRunning();

    /**
     * As long as the server is running, this method is called
     * continousely.
     */
    function handle($links);

    /**
     * Called when the server shuts down.
     */
    function shutdown();
}
