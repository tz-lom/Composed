<?php
namespace Composed;


class MethodInfo
{
    public $name;
    public $arguments;
    public $static = false;

    public function __construct($name, $arguments, $static)
    {
        $this->name = $name;
        $this->arguments = $arguments;
    }
} 