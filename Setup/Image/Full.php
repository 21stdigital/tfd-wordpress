<?php

namespace TFD\Image;

class Full extends SizeGroup
{

    public function srcSetData()
    {
        return  [
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
        ];
    }

    public function sizesData()
    {
        return [
            '100vw'
        ];
    }
}