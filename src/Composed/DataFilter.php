<?php

namespace Composed;

abstract class DataFilter
{
    protected $data;

    public function __construct()
    {
        $this->data = CacheManager::instance()->getCachedFilterData(get_class($this));
    }

    abstract public function prepareData(ClassesInfo $ci);

    public function getData()
    {
        return $this->data;
    }
}
