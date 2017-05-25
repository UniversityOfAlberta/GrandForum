<?php

$extras = $config->getValue('reportingExtras');
require_once("Report/AbstractReport.php");
require_once("PDFGenerator/PDFGenerator.php");
require_once("ReportArchive/ReportArchive.php");
if($extras['AdminVisualizations']){
    require_once("AdminVisualizations/AdminVisualizations.php");
}

?>
