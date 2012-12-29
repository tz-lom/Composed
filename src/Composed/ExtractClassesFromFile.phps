<?php

function help($execName)
{
    echo 'Usage: ',$execName,' autoloader_file PHP_file ','\n';
    exit(1);
}

if($argc!=3) help($argv[0]);


// snapshot system
$systemClasses = get_declared_classes();
$systemInterfaces = get_declared_interfaces();
$systemTraits = get_declared_traits();
$systemFunctions = get_defined_functions()['user'];
$systemConstants = get_defined_constants();
$systemVars = get_defined_vars();

// include autoloader
require_once($argv[1]);
// include target file
include($argv[2]);

// snapshot new definitions
$definedClasses = array_diff(get_declared_classes(), $systemClasses);
$definedInterfaces = array_diff(get_declared_interfaces(), $systemInterfaces);
$definedTraits = array_diff(get_declared_traits(), $systemTraits);
$definedFunctions = array_diff(get_defined_functions()['user'], $systemFunctions);
$definedConstants = array_diff(get_defined_constants(), $systemConstants);

echo serialize(array(
                    'classes'       => $definedClasses,
                    'interfaces'    => $definedInterfaces,
                    'traits'        => $definedTraits,
                    'functions'     => $definedFunctions,
                    'constants'     => $definedConstants
               ));
exit(0);