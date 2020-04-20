<?php

namespace TFD\Image;

use JsonSerializable;

class Image implements JsonSerializable
{
    public $postType = 'attachment';
    public $id;
    public $prefix;
    public $focal_point;
    public $file_type;

    private $data = [];
    private $post;

    public $original;
    public $focalPoint;

    public $native_lazy_loading = true;

    private $viewPath;

    public function __construct($id)
    {
        if (get_post_type($id) === 'attachment' && self::isImage($id)) {
            $this->id = $id;
            $this->post = get_post($this->id);
            $this->focal_point = $this->getFocalPoint();
            $this->original = $this->getOriginal();
            $this->file_type = wp_check_filetype($this->filename);
        } else {
            throw new \Exception("Can not find image with this id [{$id}]");
        }
    }


    private function getOriginal()
    {
        $image = self::isDynamicResizeEnabled()
            ? fly_get_attachment_image_src($this->id, 'full')
            : wp_get_attachment_image_src($this->id, 'full');
        if (isset($image) && is_array($image) && !empty($image)) {
            list($src, $w, $h) = $image;
            return (object)[
                'src' => $src,
                'width' => $w,
                'height' => $h,
            ];
        }
        return null;
    }

    private function getFocalPoint()
    {
        $focalPoint = $this->getMeta('theiaSmartThumbnails_position');
        $x = isset($focalPoint) && !empty($focalPoint) ? $focalPoint[0] : .5;
        $y = isset($focalPoint) && !empty($focalPoint) ? $focalPoint[1] : .5;
        return (object)[
            'x' => $x,
            'y' => $y,
            'bg_pos' => $x * 100 . '%' . $y * 100 . '%',
            'bg_pos_x' => $x * 100 . '%',
            'bg_pos_y' => $y * 100 . '%',
        ];
    }


    public function draw($size_group = null, $class = "")
    {
        $class = apply_filters('tfd_image_class', $class);
        return $this->drawImage($size_group, $class);
    }

    public function cloudUrl($srcset)
    {
        // $cloud_type = apply_filters('tfd_image_cloud_type', 'cloudinary-fetch');
        // $cloud_type = apply_filters('tfd_image_cloud_type', 'cloudinary-autoupload');
        $cloud_type = apply_filters('tfd_image_cloud_type', 'fly-dynamic');
        if ('cloudinary-autoupload' === $cloud_type) {
            $url = rtrim(apply_filters('tfd_image_cloud_url', 'https://res.cloudinary.com'), "/");
            $cloud_name = rtrim(apply_filters('tfd_image_cloud_name', 'tfd'), "/");
            $upload_folder = trim(rtrim(apply_filters('tfd_image_cloud_upload_folder', '/stage/uploads'), "/"), "/");
            $res = $url . '/' . $cloud_name . '/' . $srcset['transformations'] . '/' . $upload_folder . '/' . $this->upload_src;
            return $res;
        }

        if ('cloudinary-fetch' === $cloud_type) {
            $url = apply_filters('tfd_image_cloud_url', 'https://res.cloudinary.com');
            $cloud_name = apply_filters('tfd_image_cloud_name', 'tfd');
            return $url . '/' . $cloud_name . '/image/fetch/' . $srcset['transformations'] . '/' . urlencode($this->src);
        }

        if ('fly-dynamic' === $cloud_type && function_exists('fly_get_attachment_image')) {
            $transformations = explode(",", $srcset['transformations']);
            $crop = in_array('c_fill', $transformations) || in_array('c_limit', $transformations);
            $image = fly_get_attachment_image_src($this->id, [$srcset['width'], $srcset['height']], $crop);
            return count($image) && array_key_exists('src', $image) ? $image['src'] : null;
        }

        if ('rewrite' === $cloud_type) {
            $image = fly_get_attachment_image_src($this->id, [$srcset['width'], $srcset['height']], true);
            return count($image) && array_key_exists('src', $image) ? $image['src'] : null;
        }

        $image = wp_get_attachment_image_src($this->id, "{$srcset['width']}x{$srcset['height']}");
        // $image = wp_get_attachment_image_src($this->id, [$srcset['width'], $srcset['height']]);
        if (is_array($image) && count($image)) {
            return $image[0];
        }

        return $this->src;
    }

    private function getSrcset(SizeGroup $size_group)
    {
        return implode(', ', array_map(function ($srcset) {
            return $this->cloudUrl($srcset) . " {$srcset['width']}w";
        }, $size_group->srcset));
    }

    public function drawImage($size_group = null, $class = "")
    {
        $attributes = [
            'src' => $this->src,
            'alt' => $this->alt,
        ];

        // Native Lazy Loading
        // https://css-tricks.com/native-lazy-loading/
        if ($this->native_lazy_loading) {
            // lazy: is a good candidate for lazy loading.
            // eager: is not a good candidate for lazy loading. Load right away.
            // auto: browser will determine whether or not to lazily load.
            $attributes['loading'] = 'lazy';
        }

        if ($class) {
            $attributes['class'] = $class;
        }

        if ($size_group) {
            $attributes['srcset'] = $this->getSrcset($size_group);
            $attributes['sizes'] = $size_group->sizes;
        }

        $attr = '';
        foreach ($attributes as $key => $value) {
            $attr .= "{$key}='{$value}' ";
        }
        return "<img {$attr} >";
    }

    private function renderView($view, $params = [])
    {
        extract($params);
        $view = str_replace('.', '/', $view);
        $path = $this->viewPath . $view . '.php';
        file_exists($path) ? include($path) : '';
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
                return $this->set($attribute, basename($this->original_src));

            case 'file_extension':
            case 'fileExtension':
            case 'ext':
                return $this->set($attribute, $this->file_type['ext']);

            case 'exists':
                return $this->set($attribute, file_exists($this->original_src));

            case 'original_src':
                return $this->set($attribute, wp_get_attachment_url($this->id));

            case 'src':
                return $this->set($attribute, wp_get_attachment_url($this->id));

            case 'upload_src':
                return $this->set($attribute, trim($this->src, home_url() . '/app/uploads'));

            case 'mime_type':
            case 'mimeType':
                // return $this->set($attribute, get_post_mime_type($this->id));
                return $this->set($attribute, $this->file_type['type']);

            case 'alt':
                return $this->set($attribute, $this->getMeta('_wp_attachment_image_alt'));

            case 'name':
            case 'title':
                return $this->set($attribute, get_the_title($this->id));

            case 'caption':
                return $this->set($attribute, $this->post->post_excerpt);

            case 'description':
                return $this->set($attribute, $this->post->post_content);

            case 'href':
            case 'permalink':
                return $this->set($attribute, get_permalink($this->id));

            case 'width':
                return $this->set($attribute, $this->original->width);

            case 'height':
                return $this->set($attribute, $this->original->height);

            case 'aspect_ratio':
            case 'aspectRatio':
                return $this->getAspectRatio();

            case 'orientation':
                return $this->getOrientation();

            case 'fpx':
                return $this->focal_point->x;

            case 'fpy':
                return $this->focal_point->y;
        }
    }

    public function getAspectRatio($w = null, $h = null)
    {
        $w = $w ?: $this->width;
        $h = $h ?: $this->height;
        return $h / $w;
    }

    public function getOrientation($w = null, $h = null)
    {
        $w = $w ?: $this->width;
        $h = $h ?: $this->height;
        if ($w < $h) {
            return 'portrait';
        } elseif ($w == $h) {
            return 'square';
        }
        return 'landscape';
    }

    public function _getFpx()
    {
        return $this->focalPoint->x;
    }

    public function _getFpy()
    {
        return $this->focalPoint->y;
    }


    // ----------------------------------------------------
    // SERIALIZATION
    // ----------------------------------------------------

    /**
     * Returns an array representaion of the model for serialization
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function toArray()
    {
        $model = $this->data;

        $model['filename'] = $this->filename;
        $model['file_extension'] = $this->file_extension;
        $model['exists'] = $this->exists;
        $model['original_src'] = $this->original_src;
        $model['src'] = $this->src;
        $model['upload_src'] = $this->upload_src;
        $model['permalink'] = $this->permalink;

        $model['post_type'] = $this->postType;
        $model['id'] = $this->id;
        $model['prefix'] = $this->prefix;
        $model['focal_point'] = $this->focal_point;
        $model['native_lazy_loading'] = $this->native_lazy_loading;

        return $model;
    }

    // ----------------------------------------------------
    // STATIC METHODS
    // ----------------------------------------------------
    public static function isLazyEnabled()
    {
        return apply_filters('tfd_image_lazy_loading', true);
    }

    public static function lazyClass()
    {
        return apply_filters('tfd_image_lazy_class', 'lazyload');
    }

    public static function isDynamicResizeEnabled()
    {
        return function_exists('fly_get_attachment_image_src');
    }

    public static function isCloudinaryEnabled()
    {
        return function_exists('cloudinary_url') && CLOUDINARY;
    }

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
        if (get_post_type($id) === 'attachment' && self::isImage($id)) {
            return new Image($id);
        }
        return null;
    }
    /**
     * Find featured image model by it's post ID
     *
     * @param  int $ID
     * @return Object|NULL
     */
    public static function findFeaturedImage($id)
    {
        return has_post_thumbnail($id) ? self::find((int)get_post_thumbnail_id($id)) : null;
    }

    public static function render(Image $image = null, $size_group = null, $class = "")
    {
        if ($image) {
            return $image->draw($size_group, $class);
        }

        $placeholder = apply_filters('tfd_image_placeholder', 'http://lorempixel.com/1500/1000/');
        if (is_numeric($placeholder)) {
            return self::find((int)$placeholder)->draw($size_group, $class);
        }

        if (is_string($placeholder)) {
            return "<img href='{$placeholder}' class='{$class}'>";
        }
    }
}
