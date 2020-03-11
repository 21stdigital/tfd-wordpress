<?php

namespace TFD;

add_filter('the_content', function ($content) {
    if (apply_filters('tfd_remove_wysiwyg_wrapper_tags', true)) {
        $content = preg_replace('/<p>\s*(<a .*>)?\s*(<img .* \/>)\s*(<\/a>)?\s*<\/p>/iU', '\1\2\3', $content);
        $content = preg_replace('/<p>\s*(<script.*>*.<\/script>)\s*<\/p>/iU', '\1', $content);
        $content = preg_replace('/<p>\s*(<iframe.*>*.<\/iframe>)\s*<\/p>/iU', '\1', $content);
    }
    return $content;
}, 9999);

add_filter('acf_the_content', function ($content) {
    if (apply_filters('tfd_remove_wysiwyg_wrapper_tags', true)) {
        dlog("acf_the_content IN", $content);
        $content = preg_replace('/<p>\s*(<a .*>)?\s*(<img .* \/>)\s*(<\/a>)?\s*<\/p>/iU', '\1\2\3', $content);
        $content = preg_replace('/<p>\s*(<script.*>*.<\/script>)\s*<\/p>/iU', '\1', $content);
        $content = preg_replace('/<p>\s*(<iframe.*>*.<\/iframe>)\s*<\/p>/iU', '\1', $content);
    }
    return $content;
}, 9999);

// Hide default WordPress editor
add_action('admin_init', function () {
    $post_id = $_GET['post'] ? $_GET['post'] : $_POST['post_ID'] ;
    if (!isset($post_id)) {
        return;
    }
    $template_file = get_post_meta($post_id, '_wp_page_template', true);
    $templates = apply_filters('tfd_hide_default_editor_templates', []);
    foreach ($templates as $template) {
        if ($template_file == 'views/' . $template . '.blade.php') {
            remove_post_type_support('page', 'editor');
            return;
        }
    }
});
