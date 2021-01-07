<?php

namespace TFD\CustomFields;

use StoutLogic\AcfBuilder\FieldsBuilder;
use App;

class FieldGroup extends FieldsBuilder
{
    // WPML TRANSLATION MANAGEMENT
    // wpml_cf_preferences property
    // 0 = do not translate
    // 1 = copy
    // 2 = translate
    // 3 = copy only once

    public $id = null;
    public $key = null;
    public $title = null;
    public $style = 'seamless'; // 'default'
    public $position = 'acf_after_title'; // 'normal'
    public $menu_order = 0;
    public $label_placement = 'top';
    public $instruction_placement = 'label';
    public $active = true;
    public $description = null;
    public $hide_on_screen = [
        // 'permalink',
        // 'the_content',
        'excerpt',
        'discussion',
        'comments',
        // 'revisions',
        // 'slug',
        'author',
        // 'format',
        // 'page_attributes',
        // 'featured_image',
        // 'categories',
        // 'tags',
        'send-trackbacks',
    ];


    public function __construct($id = null)
    {
        if (!is_null($id)) {
            $this->id = $id;
        }
        if (is_null($this->id)) {
            $obj = new \ReflectionClass($this);
            $this->id = $this->id ?: str_replace('-', '_', basename($obj->getFileName(), '.php'));
        }

        $args = array_filter([
            'key' => $this->key,
            'title' => $this->title,
            'style' => $this->style,
            'position' => $this->position,
            'menu_order' => $this->menu_order,
            'label_placement' => $this->label_placement,
            'instruction_placement' => $this->instruction_placement,
            'description' => $this->description,
            'hide_on_screen' => $this->hide_on_screen,
        ]);

        $args['active'] = $this->active;

        parent::__construct($this->id, $args);
    }

    public function fields()
    {
        return $this;
    }

    public function setPageTemplateLocation($template)
    {
        return $this->setLocation('page_template', '==', "views/{$template}.blade.php");
    }

    public function location()
    {
        return $this;
    }

    public function build()
    {
        if (function_exists('acf_add_local_field_group')) {
            $this->fields()->location();
            return acf_add_local_field_group(parent::build());
        }
    }

    public static function partial(string $partial)
    {
        $path = array_map(function ($item) {
            return ucfirst($item);
        }, explode('.', $partial));
        $class = array_reduce($path, function ($carry, $item) {
            $carry .= '\\' . $item;
            return $carry;
        }, 'App\\Setup\Fields\\Partials');
        return new $class;
        // $partial = str_replace('.', '/', $partial);
        // $partial = ucfirst($partial);
        // return include_once(App\config('theme.dir')."/app/Setup/Fields/Partials/{$partial}.php");
    }


    public static function wrapper($w = 50, $class = "", $id = "")
    {
        return [
            'width' => $w,
            'class' => $class,
            'id' => $id,
        ];
    }

    public static function register($field_groups)
    {
        if (function_exists('acf_add_local_field_group') && function_exists('add_action')) {
            add_action('acf/init', function () use ($field_groups) {
                foreach ($field_groups as $field_group) {
                    if ($field_group instanceof FieldGroup) {
                        $field_group->build();
                    }
                }
            });
        }
    }
}
