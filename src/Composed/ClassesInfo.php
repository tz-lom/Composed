<?php

namespace Composed;

class ClassesInfo
{
    protected $classes;
    protected $interfaces;
    protected $traits;
    protected $functions;
    protected $constants;

    public function __construct($data)
    {
        $this->classes = $data['classes'];
        $this->interfaces = $data['interfaces'];
        $this->traits = $data['traits'];
        $this->functions = $data['functions'];
        $this->constants = $data['constants'];
    }

    public function classNames()
    {
        return $this->classes;
    }

    public function interfaceNames()
    {
        return $this->interfaces;
    }

    public function traitNames()
    {
        return $this->traits;
    }

    public function functionNames()
    {
        return $this->functions;
    }

    public function constantNames()
    {
        return $this->constants;
    }

    public function classesFromNamespace($namespace, $strict = false, $pear = false)
    {
        if($pear)
        {
            $regexp = $strict? "@^$namespace\\[^^]+$@" : "@^$namespace\\@";
        }
        else
        {
            $regexp = $strict? "@^${namespace}_[^^_]+$@" : "@^${namespace}_@";
        }

        return preg_filter(
            $regexp,
            '$0',
            $this->classes
        );
    }

    public function classesDerivedFrom($name)
    {
        return array_filter(
            $this->classes,
            function($class) use ($name) {
                try {
                    $r = new \ReflectionClass($class);
                    if($r->isSubclassOf($name))
                    {
                        return true;
                    }
                } catch (\ReflectionException $e) {
                    // suppres exception
                }
                return false;
            }
        );
    }
}
