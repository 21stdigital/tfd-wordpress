<?php
/**
 * Plugin Name:  TFD Wordpress
 * Plugin URI:   https://21st.digital
 * Description:  21st digital Wordpress helper functions in conjuntion with roots/sage
 * Version:      1.0.0
 * Author:       21st digital
 * Author URI:   https://21st.digital
 * License:      MIT License
 */

if (! defined('ABSPATH')) {
    exit;
} // Exit if accessed directly


/**
 * TFD required files
 *
 * Add or remove files to the array as needed.
 */
array_map(function ($file) {
    $file = "Setup/{$file}.php";
    require_once($file);
}, ['debug', 'acf', 'helpers', 'filters', 'wysiwyg']);
