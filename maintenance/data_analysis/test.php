<?php

require_once('../commandLine.inc');

if(count($args) > 0){
    if($args[0] == "help"){
        showHelp();
        exit;
    }
}

$objPHPExcel = new PHPExcel();
echo wfTimestampNow();
echo "\n";
?>
