<?php

namespace TFD\CustomFields;

class FieldModule
{
    protected $data = [];
    public $name;

    public $attributes = [];

    public function __construct($data)
    {
        $this->data = array_merge($this->data, $data);
        $this->name = $this->name ?: $this->data['acf_fc_layout'];
    }

    public function getId()
    {
        return $this->name ?: $this->data['acf_fc_layout'];
    }

    public function toArray()
    {
        $res = $this->data;
        $res['name'] = $this->name;
        $res['id'] = $this->getId();

        foreach ($this->attributes as $attribute) {
            if ($this->isAttribute($attribute)) {
                $value = call_user_func_array([$this, $this->funcNameForAttribute($attribute)], [$this->get($attribute)]);
                $res[$attribute] = $value;
            }
        }
        return $res;
    }

    private function isAttribute($name)
    {
        return in_array($name, $this->getAttributes())
            && method_exists($this, $this->funcNameForAttribute($name));
    }

    private function getAttributes()
    {
        return array_merge($this->attributes, ['name']);
    }

    private function funcNameForAttribute($attribute)
    {
        $func = 'get' . array_reduce(explode("_", $attribute), function ($carry, $item) {
            $carry .= ucfirst($item);
            return $carry;
        }, '');
        return $func;
    }

    public function __get($attribute)
    {
        if ('id' === $attribute) {
            return $this->getId();
        }

        if (in_array($attribute, $this->getAttributes())) {
            $func = $this->funcNameForAttribute($attribute);
            if (method_exists($this, $func)) {
                return call_user_func_array([$this, $func], [$this->get($attribute)]);
            }
        } elseif (array_key_exists($attribute, $this->data)) {
            return $this->data[$attribute];
        }
        return null;
    }


    // -----------------------------------------------------
    // GETTERS & SETTERS
    // -----------------------------------------------------
    /**
     * Get property of model or $default
     *
     * @param  property $attribute [description]
     * @param  property $default
     * @return mixed
     *
     * @todo  investagte this method
     */
    public function get($attribute, $default = null)
    {
        switch ($attribute) {
            case 'name':
                return $this->name;
            default:
                if (isset($this->data[$attribute])) {
                    return $this->data[$attribute];
                } else {
                    return $default;
                }
        }
    }

    /**
     * Set propert of the model
     *
     * @param string $attribute
     * @param string $value
     * @return void
     */
    public function set($attribute, $value)
    {
        if (in_array($attribute, $this->getAttributes())) {
            $this->data[$attribute] = $value;
        }
    }
}
