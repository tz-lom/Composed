<?php

function help($execName)
{
    echo 'Usage: ',$execName,' basePath cacheFile ','\n';
    exit(1);
}

if($argc!=3) help($argv[0]);

$basepath = $argv[1];
$cacheFile = $argv[2];
$autoload = $basepath.'/vendor/autoload.php';

/**
 * @var Composer\Autoload\ClassLoader $loader
 */
$loader = require_once($autoload);

function extractClassesFromFile($file, $autoload)
{
    $execString = sprintf('php %s %s %s',escapeshellarg(__DIR__.'/ExtractClassesFromFile.phps'),escapeshellarg($autoload), escapeshellarg($file));

    $pipesDescription = array(
        0 => array("pipe", "r"),
        1 => array("pipe", "w"),
        2 => array("pipe", "w")
    );
    $proc = proc_open($execString, $pipesDescription, $pipes);

    fclose($pipes[0]);
    $output = '';
    while(!feof($pipes[1])) $output.=fread($pipes[1],1024);
    fclose($pipes[1]);
    fclose($pipes[2]);
    proc_close($proc);

    if(false == $data = @unserialize($output)) $data = array();
    return $data;
}


$data = array();


foreach($loader->getClassMap() as $class=>$file)
{
    $data = array_replace_recursive(extractClassesFromFile($file, $autoload),$data);
}

foreach($loader->getPrefixes() as $prefix=>$dirs)
{
    foreach($dirs as $dir)
    {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

        /**
         * @var SplFileInfo $fileInfo
         */
        foreach($files as $fileInfo)
        {
            if($fileInfo->getExtension()=='php')
            {
                $data = array_replace_recursive(extractClassesFromFile($fileInfo->getPathname(), $autoload),$data);
            }
        }
    }
}

// normalize indexes

foreach($data as $key=>$values)
{
    $data[$key] = array_values($values);
}

// regenerate caches

use Composed\ClassesInfo;
use Composed\CacheManager;

$classesInfo = new ClassesInfo($data);
$filters = array();

foreach($classesInfo->classesDerivedFrom('Composed\\DataFilter') as $className)
{
    $r = new \ReflectionClass($className);
    $filter = $r->newInstanceWithoutConstructor();

    $filter->prepareData($classesInfo);

    $filters[$className] = $filter->getData();
}

\Composed\CacheManager::instance()->saveToCache($classesInfo, $filters);
