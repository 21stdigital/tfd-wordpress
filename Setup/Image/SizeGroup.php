<?php

namespace TFD\Image;

class Size
{
    public static function register(array $srcset, array $sizes)
    {
        dlog($srcset);
        dlog($sizes);
    }
}

// Info
// https://cloudinary.com/blog/automatic_responsive_images_with_client_hints

Size::register(
    [
        'default' => [
            'output' => false,
            'dpi' => 2,
            'effect' => null,
            'fetch_format' => 'auto',
            'quality' => 'auto',
            'crop' => 'fill', //'fit', 'limit,
            'gravity' => 'faces:auto', //'faces', 'auto',
        ],
        'xl' => [
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