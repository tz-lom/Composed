<?php

function help($execName)
{
    echo 'Usage: ',$execName,' basePath cacheDir ','\n';
    exit(1);
}

if($argc!=3) help($argv[0]);

$basepath = $argv[1];
$cacheDir = $argv[2];
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


$data = array('hash' => hash_file('sha256', $basepath.'/composer.lock'));

function mergeExtractedInfo($extracted, $data)
{
    foreach($extracted as $key=>$list)
    {
        foreach($list as $item=>$info)
        {
            if(isset($data[$key][$item])) continue;
            $data[$key][$item] = $info;
        }
    }
    return $data;
}

foreach($loader->getClassMap() as $class=>$file)
{
    $data = mergeExtractedInfo(extractClassesFromFile($file, $autoload),$data);
}

foreach($loader->getPrefixes() as $prefix=>$dirs)
{
    foreach($dirs as $dir)
    {
        $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir));

        /**
         * @var SplFileInfo $fileInfo
         */
        foreach($files as $fileInfo)
        {
            if($fileInfo->getExtension()=='php')
            {
                echo $fileInfo->getPathname(),"\n";
                $data = mergeExtractedInfo(extractClassesFromFile($fileInfo->getPathname(), $autoload),$data);
            }
        }
    }
}

file_put_contents($cacheDir.'classes',serialize($data));