<?php

namespace TFD\CustomFields;

class FieldModule
{
    private $data = [];
    public $name;

    public function __construct($data)
    {
        $this->data = array_merge($this->data, $data);
        $this->name = $this->name ?: $this->data['acf_fc_layout'];
    }

    public function toArray()
    {
        $res = $this->data;
        $res['name'] = $this->name;
        return $res;
    }
}
