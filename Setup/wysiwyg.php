<?php

add_filter('acf/fields/wysiwyg/toolbars', function ($toolbars) {
    $toolbars['Very Simple' ] = array();
    $toolbars['Very Simple' ][1] = array('bold' , 'italic' , 'underline', 'link' );

    $toolbars['Only Link' ] = array();
    $toolbars['Only Link' ][1] = array('link');

    return $toolbars;
});

/**
 * ACF-autosize Plugin
 * More info: https://github.com/yeah8000/acf-autosize
 *  */
add_filter('acf-autosize/wysiwyg/min-height', function () {
    return 100;
});



/**
 * Add height field to ACF WYSIWYG
 */
// function wysiwyg_render_field_settings($field)
// {
// }
// add_action('acf/render_field_settings/type=wysiwyg', 'wysiwyg_render_field_settings', 10, 1);

/**
 * Render height on ACF WYSIWYG
 */
// function wysiwyg_render_field($field)
// {
// }
// add_action('acf/render_field/type=wysiwyg', 'wysiwyg_render_field', 10, 1);
