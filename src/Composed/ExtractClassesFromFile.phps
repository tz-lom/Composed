<?php

function help($execName)
{
    echo 'Usage: ',$execName,' autoloader_file PHP_file ','\n';
    exit(1);
}

if($argc!=3) help($argv[0]);

// include autoloader
require_once($argv[1]);

// snapshot system
$systemClasses = get_declared_classes();
$systemInterfaces = get_declared_interfaces();
$systemTraits = get_declared_traits();
$systemFunctions = get_defined_functions()['user'];
$systemConstants = get_defined_constants();
$systemVars = get_defined_vars();

// include target file
include($argv[2]);

// snapshot new definitions
$definedClasses = array_diff(get_declared_classes(), $systemClasses);
$definedInterfaces = array_diff(get_declared_interfaces(), $systemInterfaces);
$definedTraits = array_diff(get_declared_traits(), $systemTraits);
$definedFunctions = array_diff(get_defined_functions()['user'], $systemFunctions);
$definedConstants = array_diff(get_defined_constants(), $systemConstants);

$fullDefinedClasses = array();
foreach($definedClasses as $className)
{
    $class = new \ReflectionClass($className);

    $implements = $class->getInterfaceNames();
    $traits = $class->getTraitNames();
    if(NULL != $extends = $class->getParentClass()) $extends = $extends->getName();

    $fullDefinedClasses[$className] = array(
        'name'          => $class->getName(),
        'shortname'     => $class->getShortName(),
        'namespace'     => $class->getNamespaceName(),
        'extends'       => $extends,
        'implements'    => $implements,
        'traits'        => $traits
    );
}

$fullDefinedInterfaces = array();
foreach($definedInterfaces as $interfaceName)
{
    $interface = new \ReflectionClass($interfaceName);

    $fullDefinedInterfaces[$interfaceName] = array(
        'name'          => $interface->getName(),
        'shortname'     => $interface->getShortName(),
        'namespace'     => $interface->getNamespaceName(),
        'implements'    => $interface->getInterfaceNames(),
        'traits'        => $interface->getTraitNames()
    );
}

$fullDefinedTraits = array();
foreach($definedTraits as $traitName)
{
    $trait = new \ReflectionClass($traitName);
    $fullDefinedTraits[$traitName] = array(
        'name'      => $trait->getName(),
        'shortname' => $trait->getShortName(),
        'namespace' => $trait->getNamespaceName(),
        'traits'    => $trait->getTraitNames()
    );
}

echo serialize(array(
                    'classes'       => $fullDefinedClasses,
                    'interfaces'    => $fullDefinedInterfaces,
                    'traits'        => $fullDefinedTraits,
                    'functions'     => $definedFunctions,
                    'constants'     => $definedConstants
               ));
exit(0);