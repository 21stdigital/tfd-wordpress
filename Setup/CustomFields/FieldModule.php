<?php

namespace TFD\CustomFields;

class FieldModule
{
    private $data = [];

    public function __construct($data)
    {
        $this->data = array_merge($this->data, $data);
    }
}
