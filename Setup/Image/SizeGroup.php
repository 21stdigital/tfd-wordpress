<?php

namespace TFD\Image;

/************************************************************
# Responsive Images Done Right: A Guide To And srcset
# https://www.smashingmagazine.com/2014/05/responsive-images-done-right-guide-picture-srcset/

# Responsive Images – <picture>, srcset, sizes & Co.
# https://blog.kulturbanause.de/2014/09/responsive-images-srcset-sizes-adaptive/

# Responsive Breakpoints
# https://www.responsivebreakpoints.com/

*/

class SizeGroup
{
    public $sourcesData = [];
    public $srcsetData = [];
    public $sizesData = [];

    public function sourcesData()
    {
        return [];
    }

    public function srcsetData()
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
            case 'sources':
                return $this->getSources();
            case 'srcset':
                return $this->getSrcset();
            case 'sizes':
                return implode(', ', $this->getSizes());
        }
        return null;
    }

    private function getSources()
    {
        $sourcesData = $this->sourcesData();
        if (array_key_exists('default', $sourcesData) && is_array($sourcesData['default'])) {
            $default = ['default' => $sourcesData['default']];
        }

        if (is_array($sourcesData) && array_key_exists('sources', $sourcesData) && is_array($sourcesData['sources'])) {
            $sources = array_filter(array_map(function ($source) use ($default) {
                if (!array_key_exists('srcset', $source) || !count($source['srcset'])) {
                    return null;
                }
                $source['scrset'] = $this->getSrcset(array_merge($default, $source['srcset']));
                return $source;
            }, $sourcesData['sources']));
            dlog($sources);
            return $sources;
        }
        return null;
    }

    private function getSrcset($srcsetData = null)
    {
        $srcsetData = $srcsetData ?: $this->srcsetData();
        $srcset = array_filter(array_map(function ($src) use ($srcsetData) {
            if (array_key_exists('output', $src) && false === $src['output']) {
                return null;
            }
            if (array_key_exists('extends', $src) && $src['extends'] && array_key_exists($src['extends'], $srcsetData) && is_array($srcsetData[$src['extends']])) {
                $params = array_filter(array_merge($srcsetData[$src['extends']], $src));
                return [
                    'width' => $params['width'],
                    'height' => $params['height'],
                    'transformations' => $this->buildTransformationSlug($params)
                ];
            }
        }, $srcsetData));
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
    public function buildTransformationSlug($args = [])
    {
        if (empty($args)) {
            return '';
        }

        $cloudinary_params = [
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
        ];

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
        if (('w' === $key || 'h' === $key) && empty($value)) {
            return false;
        }
        return true;
    }

    public static function em($px)
    {
        return $px / 16 . 'em';
    }

    public static function minWidth($width)
    {
        return "(min-width: {$width})";
    }
}
