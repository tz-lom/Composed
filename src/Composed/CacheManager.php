<?php

namespace tzlom\Composed;

class CacheManager
{
    protected $basepath;
    protected $cacheDir;
    /**
     * @var CacheManager
     */
    protected static $instance = NULL;
    protected $data = NULL;
    protected $denyRebuild = false;

    private function __construct()
    {
        $this->basepath = dirname(dirname(dirname(__DIR__)));
        $this->cacheDir = __DIR__.'/cache/';
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

    public function ensureCache()
    {
        $checksum = hash_file('sha256', $this->basepath.'/composer.lock');

        if($this->loadCache())
        {
            if($this->data['hash'] != $checksum)
            {
                $this->rebuildCache();
                if($this->loadCache()) return;
            }
            else
            {
                return;
            }

        }
        throw new \ErrorException("Cannot load cache");
    }

    public function rebuildCache()
    {
        if($this->denyRebuild) throw new \ErrorException('Classes structure updated but cache not regenerated, you need to do this manually');

        exec(sprintf('php %s %ы',
            escapeshellarg(__DIR__.'/ExtractComposedClasses.phps'),
            escapeshellarg($this->basepath),
            escapeshellarg($this->cacheDir)
        ));
    }

    protected function loadCache()
    {
        $data = @unserialize(file_get_contents($this->cacheDir.'classes'));
        if($data == false) return false;
        $this->data = $data;
        return true;
    }
}