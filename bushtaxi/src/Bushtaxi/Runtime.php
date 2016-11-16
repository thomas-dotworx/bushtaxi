<?php

namespace Bushtaxi;

interface Runtime
{
    function init();
    function isRunning();
    function handle($links);
}