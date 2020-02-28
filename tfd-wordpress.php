<?php

use TFD\Debug;

if (!function_exists('dlog')) {
    function dlog()
    {
        Debug\Debug::log(debug_backtrace());
    }
}

if (!function_exists('console_log')) {
    function console_log()
    {
        Debug\Debug::console(debug_backtrace());
    }
}