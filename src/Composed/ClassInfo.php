<?php

namespace Composed;


class ClassInfo
{
    public $name;
    public $extends = NULL;
    public $implements = [];
    public $methods = [];
    public $constants = [];
    public $abstract = false;
    public $final = false;

    public function __construct($name, $extends, $implements, $methods, $constants, $abstract, $final)
    {
        $this->name = $name;
        $this->extends = $extends;
        $this->implements = $implements;
        $this->methods = $methods;
        $this->constants = $constants;
        $this->abstract = $abstract;
        $this->final = $final;
    }
} 