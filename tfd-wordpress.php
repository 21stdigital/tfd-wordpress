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

if (class_exists('\Whoops\Run')) {
    if (defined('WP_ENV') && WP_ENV !== 'production') {
        $whoops = new \Whoops\Run;
        $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
        $whoops->register();
    }
}
