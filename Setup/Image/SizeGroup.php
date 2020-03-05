<?php

namespace TFD\Image;

class SizeGroup
{
    public $srcsetData = [];
    public $sizesData = [];


    public function srcSetData()
    {
        return [];
    }

    public function sizesData()
    {
        return [];
    }


    public function __get(string $attribute)
    {
        switch ($attribute) {
            case 'srcset':
                return $this->getSrcset();
            case 'sizes':
                return $this->getSizes();
        }
        return null;
    }


    private static function getSrcset()
    {
        $srcset = array_filter(array_map(function ($src) {
            if (array_key_exists('output', $src) && false === $src['output']) {
                return null;
            }
            if (array_key_exists('extends', $src) && $src['extends'] && array_key_exists($src['extends'], $this->srcset) && is_array($this->srcset[$src['extends']])) {
                $params = array_merge($this->srcset[$src['extends']], $src);
                return $this->buildTransformationSlug($params);
            }
        }, $this->srcsetData()));
        return $srcset;
    }

    private function getSizes()
    {
        return $this->sizesData();
    }

    /**
     * Build a Cloudinary transformation slug from arguments.
     *
     * @param  array $args
     * @return string
     */
    public function buildTransformationSlug($args = array()) {
        if (empty($args)) {
            return '';
        }

        $cloudinary_params = array(
            'angle'                => 'a',
            'aspect_ratio'         => 'ar',
            'background'           => 'b',
            'border'               => 'bo',
            'crop'                 => 'c',
            'color'                => 'co',
            'dpr'                  => 'dpr',
            'duration'             => 'du',
            'effect'               => 'e',
            'end_offset'           => 'eo',
            'flags'                => 'fl',
            'height'               => 'h',
            'overlay'              => 'l',
            'opacity'              => 'o',
            'quality'              => 'q',
            'radius'               => 'r',
            'start_offset'         => 'so',
            'named_transformation' => 't',
            'underlay'             => 'u',
            'video_codec'          => 'vc',
            'width'                => 'w',
            'x'                    => 'x',
            'y'                    => 'y',
            'zoom'                 => 'z',
            'audio_codec'          => 'ac',
            'audio_frequency'      => 'af',
            'bit_rate'             => 'br',
            'color_space'          => 'cs',
            'default_image'        => 'd',
            'delay'                => 'dl',
            'density'              => 'dn',
            'fetch_format'         => 'f',
            'gravity'              => 'g',
            'prefix'               => 'p',
            'page'                 => 'pg',
            'video_sampling'       => 'vs',
            'progressive'          => 'fl_progressive',
        );

        $slug = [];
        foreach ($args as $key => $value) {
            if (array_key_exists($key, $cloudinary_params) && $this->validValue($cloudinary_params[$key], $value)) {
                switch ($key) {
                    case 'progressive':
                        if (true === $value) {
                            $slug[] = $cloudinary_params[ $key ];
                        } else {
                            $slug[] = $cloudinary_params[ $key ] . ':' . $value;
                        }
                        break;
                    default:
                        $slug[] = $cloudinary_params[ $key ] . '_' . $value;
                }
            }
        }
        return implode(',', $slug);
    }

    public function validValue($key = '', $value = '')
    {
        if (( 'w' === $key || 'h' === $key ) && empty($value)) {
            return false;
        }
        return true;
    }
}

// Info
// https://cloudinary.com/blog/automatic_responsive_images_with_client_hints

SizeGroup::register(
    [
        'default' => [
            'output' => false,
            'dpr' => 2,
            'effect' => null,
            'fetch_format' => 'auto',
            'quality' => 'auto',
            'crop' => 'fill', //'fit', 'limit,
            'gravity' => 'faces:auto', //'faces', 'auto',
        ],
        'xl' => [
            'extends' => 'default',
            'width' => 1440,
            'height' => 680,
        ],
        'xl' => [
            'width' => 1440 * 0.8,
            'height' => 680 * 0.8,
        ],
        'md' => [
            'width' => 1440 * 0.6,
            'height' => 680 * 0.6,
        ],
        'sm' => [
            'width' => 1440 * 0.5,
            'height' => 680 * 0.5,
        ],
        'xs' => [
            'width' => 1440 * 0.3,
            'height' => 680 * 0.3,
        ],
    ],
    [
        '100vw'
    ]
);