<?php
// The purpose of this file is to simply include the other datastructures
require_once("Addressing.php");
require_once("Blob.php");
define("WORKS_WITH", 'Works With');
define("SUPERVISES", 'Supervises');

autoload_register('GrandObjects');
autoload_register('GrandObjects/API');
$wgHooks['OutputPageParserOutput'][] = 'createModels';

global $apiRequest;
$apiRequest->addAction('Hidden','person', new PersonAPI());

function createModels($out, $parserout){
    global $wgServer, $wgScriptPath;
    $out->addScript("<script type='text/javascript' src='{$wgServer}{$wgScriptPath}/extensions/GrandObjects/BackboneModels/Person.js'></script>");
    return true;
}
?>
