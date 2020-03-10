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


/**
 * TFD required files
 *
 * Add or remove files to the array as needed.
 */
array_map(function ($file) use ($sage_error) {
    $file = "Setup/{$file}.php";
    if (!locate_template($file, true, true)) {
        $sage_error(sprintf(__('Error locating <code>%s</code> for inclusion.', 'tfd'), $file), 'File not found');
    }
}, ['debug', 'filters']);
