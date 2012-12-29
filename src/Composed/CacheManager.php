<?php

namespace Composed;

class CacheManager
{
    protected $basepath;
    protected $cacheFile;
    /**
     * @var CacheManager
     */
    protected static $instance = NULL;
    protected $denyRebuild = false;

    /**
     * @var ClassesInfo
     */
    protected $classesCache = NULL;
    protected $filteredCache = array();
    protected $hash = '';
    protected $collectedHash = '';

    private function __construct()
    {
        $this->basepath = dirname(dirname(dirname(dirname(dirname(__DIR__)))));
        $this->cacheFile = __DIR__.'/cache';
    }

    private function __clone()
    {
    }

    public static function instance()
    {
        if(self::$instance==NULL)
        {
            self::$instance = new self;
        }
        return self::$instance;
    }

    private function hashComposerState()
    {
        if(!$this->collectedHash)
        {
            $file = $this->basepath.'/composer.lock';
            if(!file_exists($file)) throw new \ErrorException('Cannot find composer.lock file');
            $this->collectedHash = @hash_file('sha256', $file);
        }
        return $this->collectedHash;
    }

    public function ensureCache()
    {
        if($this->hashComposerState() == $this->hash ) return;

        if($this->loadCache() && $this->hash==$this->collectedHash) return;

        $this->rebuildCache();
        if($this->loadCache() && $this->hash==$this->collectedHash) return;

        throw new \ErrorException("Cannot load cache");
    }

    public function rebuildCache()
    {
        if($this->denyRebuild) throw new \ErrorException('Classes structure updated but cache not regenerated, you need to do this manually');

        exec(sprintf('php %s %s %s',
            escapeshellarg(__DIR__.'/RebuildComposedCache.phps'),
            escapeshellarg($this->basepath),
            escapeshellarg($this->cacheFile)
        ));

        $this->collectedHash = '';
        $this->hash = '';
        $this->classesCache = NULL;
        $this->filteredCache = array();
    }

    protected function loadCache()
    {
        $data = @unserialize(file_get_contents($this->cacheFile));
        if($data == false) return false;

        $this->hash = $data['hash'];
        $this->classesCache = $data['classes'];
        $this->filteredCache = $data['filters'];

        return true;
    }

    public function saveToCache(ClassesInfo $classes, $filtered)
    {
        $data = array(
            'hash'      => $this->hashComposerState(),
            'classes'   => $classes,
            'filters'   => $filtered
        );

        if(file_put_contents($this->cacheFile, serialize($data))==false)
        {
            throw new \ErrorException("Cannot save data to cache file $this->cacheFile");
        }
    }

    public function getCachedFilterData($name)
    {
        $this->ensureCache();
        if(!isset($this->filteredCache[$name])) throw new \ErrorException('Asked to retrieve nonexistent filter');
        return $this->filteredCache[$name];
    }
}