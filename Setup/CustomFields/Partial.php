<?php

namespace TFD\CustomFields;

class Partial extends FieldGroup
{
    public $id = null;
    public $key = null;
    public $title = null;
    public $style = null;
    public $position = null;
    public $menu_order = null;
    public $label_placement = null;
    public $instruction_placement = null;
    public $active = false;
    public $description = null;
    public $hide_on_screen = null;

    public function __construct()
    {
        parent::__construct();
        $this->fields();
    }

    public function build()
    {
        if (function_exists('acf_add_local_field_group')) {
            $this->fields();
            $this->setGroupConfig('active', false);
            return acf_add_local_field_group(parent::build());
        }
    }
}

