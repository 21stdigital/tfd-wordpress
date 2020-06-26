<?php

if (!function_exists('dlog')) {
    function dlog()
    {
        $debug_args = debug_backtrace();
        $info = '';
        if (count($debug_args) && $debug_args[0]['function'] == 'dlog') {
            $function_name = $debug_args[0]['function'];
            $line = $debug_args[0]['line'];
            $file = $debug_args[0]['file'];
            $info = ($file && $line) ? $file . '[' . $line . ']: ' : '';
        }
        $numargs = func_num_args();

        if (true === WP_DEBUG) {
            $arg_list = func_get_args();
            $output = $info;
            for ($i = 0; $i < $numargs; $i++) {
                if (is_array($arg_list[$i]) || is_object($arg_list[$i])) {
                    $output .= print_r($arg_list[$i], true);
                } else {
                    $output .= $arg_list[$i];
                }
                if ($i + 1 < $numargs) {
                    $output .= ', ';
                }
            }
            error_log($output);
        }
    }
}

if (!function_exists('console_log')) {
    function console_log()
    {
        $data = func_get_args();
        if (function_exists('add_action')) {
            add_action('wp_head', function () use ($data) {
                echo '<script>';
                echo 'console.log('. json_encode($data) .')';
                echo '</script>';
            });
        }
    }
}


if (WP_ENV === 'development') {
    $whoops = new \Whoops\Run;
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
    $whoops->register();
}
