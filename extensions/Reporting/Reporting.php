<?php

autoload_register('Reporting/SessionData');
require_once("ReportTables/EvaluationTable.php");
require_once("Report/AbstractReport.php");
require_once("PDFGenerator/PDFGenerator.php");
require_once("ReportStats/ReportStats.php");
require_once("CreatePDF/CreatePDF.php");
require_once("ReportArchive/ReportArchive.php");
require_once("ReviewerConflicts/ReviewerConflicts.php");
require_once("Report/SpecialPages/ReportPDFs.php");
require_once("Report/SpecialPages/ReportSurvey.php");
require_once("ReviewResults/ReviewResults.php");
require_once("LoiProposals/LoiProposals.php");
require_once("SanityChecks/SanityChecks.php");
require_once("AdminVisualizations/AdminVisualizations.php");

?>
