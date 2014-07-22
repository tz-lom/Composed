<?php

namespace Composed;


class ArgumentInfo
{
    public $name;
    public $type = NULL;
    public $default = false;

    public function __construct($name, $type, $default)
    {
        $this->name = $name;
        $this->type = $type;
        $this->default = $default;
    }
} 