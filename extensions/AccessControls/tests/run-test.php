<?php
$wgUnitTestMode = true;

/*$wgUnitTestPrefix = $wgDBprefix;*/

$temp = $_SERVER;
unset ($_SERVER);
require_once( dirname(__FILE__) . '/../../../maintenance/commandLine.inc' );
$_SERVER = $temp;
//$path = '../../../';
//set_include_path(get_include_path() . PATH_SEPARATOR . $path);
//require_once( 'index.php' );
require( 'PHPUnit/TextUI/Command.php' );

?>