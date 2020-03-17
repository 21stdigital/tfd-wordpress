<?php

namespace TFD\Models;

class Attachment
{
    public $postType = 'attachment';
    public $id;
    public $prefix;

    private $data = [];
    private $post;

    public $virtual = [
        'name',
        'caption',
        'description',
        'href',
        'src',
        'originalSrc',
        'width',
        'height',
        'aspectRatio',
        'orientation',
        'fpx',
        'fpy',
        'mimeType',
        'originalSrc'
    ];


    public function __construct($id)
    {
        if (get_post_type($id) === 'attachment') {
            $this->id = $id;
            $this->post = get_post($this->id);
        } else {
            throw new \Exception("Can not find the attachment with this id [{$id}]");
        }
    }

    public function downloadLink($label = '', $class = null)
    {
        $attributes = array_filter([
            'href' => $this->src,
            'download' => $this->title,
            'class' => $class,
        ]);

        $attr = '';
        foreach ($attributes as $key => $value) {
            $attr .= "{$key}='{$value}' ";
        }

        $label = $label ?: $this->filename;
        return "<a {$attr}>{$label}</a>";
    }

    // ----------------------------------------------------
    // PROPERTIES
    // ---------------------------------------------------
    /**
     * Returns meta value for a meta key
     *
     * @param  string meta_key
     * @return string
     */
    public function getMeta($key)
    {
        return get_post_meta($this->id, ($this->prefix.$key), true);
    }

    private function get($attribute)
    {
        if (array_key_exists($attribute, $this->data) && !is_null($this->data[$attribute])) {
            return $this->data[$attribute];
        }
        return null;
    }

    private function set($attribute, $value)
    {
        $this->data[$attribute] = $value;
        return $value;
    }

    public function __get(string $attribute)
    {
        if ($res = $this->get($attribute)) {
            return $res;
        }

        switch ($attribute) {
            case 'filename':
                return $this->set($attribute, $this->getMeta('_wp_attached_file'));

            case 'src':
                return $this->set($attribute, wp_get_attachment_url($this->id));

            case 'mime_type':
            case 'mimeType':
                return $this->set($attribute, $this->post->post_mime_type);

            case 'name':
            case 'title':
                return $this->set($attribute, $this->post->post_title);

            case 'caption':
                return $this->set($attribute, $this->post->post_excerpt);

            case 'description':
                return $this->set($attribute, $this->post->post_content);

            case 'href':
            case 'permalink':
                return $this->set($attribute, get_permalink($this->id));

            case 'path':
                return $this->set($attribute, get_attached_file($this->id));

            case 'filesize':
                return filesize($this->path);

            case 'size':
                return $this->set($attribute, size_format(filesize($this->path)));
        }
    }

    // ----------------------------------------------------
    // STATIC METHODS
    // ----------------------------------------------------
    public static function isSVG($id)
    {
        return is_numeric($id) ? get_post_mime_type((int)$id) === 'image/svg+xml' : false;
    }

    public static function isJPG($id)
    {
        return is_numeric($id) ? get_post_mime_type((int)$id) === 'image/jpeg' : false;
    }

    public static function isPNG($id)
    {
        return is_numeric($id) ? get_post_mime_type((int)$id) === 'image/png' : false;
    }

    public static function isGIF($id)
    {
        return is_numeric($id) ? get_post_mime_type((int)$id) === 'image/gif' : false;
    }

    public static function isImage($id)
    {
        if (!is_numeric($id)) {
            return false;
        }
        switch (get_post_mime_type((int)$id)) {
            case 'image/svg+xml':
            case 'image/jpeg':
            case 'image/png':
            case 'image/gif':
            case 'image/webp':
                return true;

            default:
                return false;
        }
    }

    public static function toMimeType($extension = 'jpg')
    {
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                return 'image/jpeg';
            case 'png':
                return 'image/png';
            case 'gif':
                return 'image/gif';
            case 'webp':
                return 'image/webp';
            case 'svg':
                return 'image/svg+xml';
        }
        return '';
    }

    public static function find($id)
    {
        if (get_post_type($id) === 'attachment') {
            return new Attachment($id);
        }
        return null;
    }
}
