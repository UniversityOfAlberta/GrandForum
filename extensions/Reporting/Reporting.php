<?php

$extras = $config->getValue('reportingExtras');
require_once("Report/AbstractReport.php");
require_once("PDFGenerator/PDFGenerator.php");
require_once("ReportArchive/ReportArchive.php");
if($extras['EvaluationTable']){
    require_once("ReportTables/EvaluationTable.php");
}
if($extras['ReportStats']){
    require_once("ReportStats/ReportStats.php");
}
if($extras['CreatePDF']){
    require_once("CreatePDF/CreatePDF.php");
}
if($extras['ReviewResults']){
    require_once("ReviewResults/ReviewResults.php");
}
if($extras['AdminVisualizations']){
    require_once("AdminVisualizations/AdminVisualizations.php");
}

?>
