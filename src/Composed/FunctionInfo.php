<?php

namespace Composed;


class FunctionInfo
{
    public $name;
    public  $arguments;

    public function __construct($name, $arguments)
    {
        $this->name = $name;
        $this->arguments = $arguments;
    }
} 