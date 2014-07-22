<?php

namespace Composed;


class FileScanner
{
    /**
     * @var ClassInfo[]
     */
    public $classes = [];

    /**
     * @var TraitInfo[]
     */
    public $traits = [];

    /**
     * @var InterfaceInfo[]
     */
    public $interfaces = [];

    /**
     * @var FunctionInfo[]
     */
    public $functions = [];

    /**
     * @var ConstInfo[]
     */
    public $constants = [];

    public function __construct($path)
    {
        if(!$path) return;
        foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $file)
        {
            /**
             * @var \SplFileInfo $file
             */
            if($file->getExtension() === 'php')
            {
                $this->traverseFile($file->getRealPath());
            }
        }
    }

    protected function traverseFile($file)
    {
        $code = file_get_contents($file);
        $parser = new \PHPParser_Parser(new \PHPParser_Lexer);
        $stmt = $parser->parse($code);
        $de = new DeclarationsExtractor($stmt);

        $this->merge($de);
    }

    protected function merge($de)
    {
        $this->classes = array_merge($this->classes, $de->classes);
        $this->constants = array_merge($this->constants, $de->constants);
        $this->functions = array_merge($this->functions, $de->functions);
        $this->interfaces = array_merge($this->interfaces, $de->interfaces);
        $this->traits = array_merge($this->traits, $de->traits);
    }

    public static function currentPackage()
    {
        $obj = new self(NULL);

        foreach(new \DirectoryIterator(self::findComposerRoot()) as $dir)
        {
            /**
             * @var \DirectoryIterator $dir
             */
            if($dir->isDir() && !$dir->isDot())
            {
                if($dir->getFilename()!='vendor')
                {
                    $obj->merge(new self($dir->getRealPath()));
                }
            }
        }
        return $obj;
    }

    public static function dependantPackages()
    {
        return new self(self::findComposerRoot().'/vendor');
    }

    public static function allPackages()
    {
        $obj = self::currentPackage();
        $obj->merge(self::dependantPackages());
        return $obj;
    }

    protected static function findComposerRoot()
    {
        return /*dirname(dirname(*/dirname(dirname(__DIR__))/*))*/;
    }
} 