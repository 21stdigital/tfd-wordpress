<?php

namespace TFD\Image;

class Image
{
    public $postType = 'attachment';
    public $id;
    public $prefix;
    public $focal_point;

    private $data;
    private $post;

    public $original;
    public $focalPoint;
    private $sizeGroup;

    private $viewPath;


    public $virtual = [
        'name',
        'alt',
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
        if (is_attachment($id) && self::isImage($id)) {
            $this->id = $id;
            $this->post = get_post($this->id);
            $this->focal_point = $this->getFocalPoint();
        } else {
            throw new \Exception("Can not find image with this id [${$id}]");
        }
    }


    private function getOriginal()
    {
        $image = self::isDynamicResizeEnabled()
            ? fly_get_attachment_image_src($this->ID, 'full')
            : wp_get_attachment_image_src($this->ID, 'full');
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


    public function draw($sizeGroup = null)
    {

        // $defaultClasses = apply_filters('tfd_image_classes', ['Image']);

        if ($sizeGroup) {
            return '';
        } else {
            $this->drawImage();
        }
    }



    public function drawImage($sizeGroup = null)
    {

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
            dlog($res);
            return $res;
        }

        switch ($attribute) {
            case 'original_src':
                return $this->set($attribute, wp_get_attachment_url($this->id));

            case 'mime_type':
            case 'mimeType':
                return $this->set($attribute, get_post_mime_type($this->id));

            case 'alt':
                return $this->set($attribute, $this->getMeta('_wp_attachment_image_alt'));

            case 'name':
                return $this->set($attribute, get_the_title($this->id));

            case 'caption':
                return $this->set($attribute, $this->post->post_excerpt);

            case 'description':
                return $this->set($attribute, $this->post->post_content);

            case 'href':
            case 'permalink':
                return $this->set($attribute, get_permalink($this->id));

            case 'width':
                return 0; //TBD

            case 'height':
                return 0; //TBD

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

    /**
     * Find featured image model by it's post ID
     *
     * @param  int $ID
     * @return Object|NULL
     */
    public static function findFeaturedImage($id)
    {
        return has_post_thumbnail($id) ? Image::find((int)get_post_thumbnail_id($id)) : null;
    }
}
