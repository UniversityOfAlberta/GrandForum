<?php
if (!defined('MEDIAWIKI')) {
  echo "This file is a MediaWiki extension, and cannot be accessed independantly.";
  exit( 1 );
}

define('ANNOKI', true);

require_once("DBFunctions.php");
require_once('AnnokiConfig.php');
require_once($egAnnokiCommonPath.'/AnnokiArticleEditor.php');
require_once($egAnnokiCommonPath.'/AnnokiDatabaseFunctions.php');
require_once($egAnnokiCommonPath.'/AnnokiHTMLUtils.php');
require_once($egAnnokiCommonPath.'/AnnokiUtils.php');

/** Enumerate Annoki-based extensions.  Add new ones in a similar fashion. Example:
$egAnnokiExtensions['MyAnnokiExtension'] = array( 'name' => 'My Annoki Extension', //This show up in the Annoki extension list
					          'path' => "$IP/extensions/MyAnnokiExtension/MyAnnokiExtension.php", //The path to the main extension php file.
					          'enabled' => true, //True to use the extension, false otherwise.
					          );
**/
function autoload_register($directory){
    spl_autoload_register(function ($class) use ($directory) {
        if(file_exists(dirname(__FILE__) . "/../$directory/" . $class . '.php')){
            require_once(dirname(__FILE__) . "/../$directory/" . $class . '.php');
        }
    });
}

function redirect($url){
    session_write_close();
    header("Location: $url");
    exit;
}

$egAnnokiExtensions = array();

$egAnnokiExtensions['AccessControl'] = array( 'name' => 'Annoki Access Controls',
					      'path' => "$IP/extensions/AccessControls/AccessControls.php",
					      'enabled' => true,
					      );
					      
$egAnnokiExtensions['Messages']     = array( 'name' => 'Messages',
					      'path' => "$IP/extensions/Messages/Message.php",
					      'enabled' => true,
					      );
					      
$egAnnokiExtensions['TabUtils']     = array( 'name' => 'TabUtils',
					      'path' => "$IP/extensions/TabUtils/TabUtils.php",
					      'enabled' => true,
					      );
					      
$egAnnokiExtensions['GrandObjects']     = array( 'name' => 'GrandObjects',
					      'path' => "$IP/extensions/GrandObjects/GrandObjects.php",
					      'enabled' => true,
					      );
					      
$egAnnokiExtensions['UI'] = array( 'name' => 'User Interface',
					      'path' => "$IP/extensions/UI/UIElement.php",
					      'enabled' => true,
					      );
					      
$egAnnokiExtensions['Notification']     = array( 'name' => 'Notification',
					      'path' => "$IP/extensions/Notification/Notification.body.php",
					      'enabled' => true,
					      );
					      
$egAnnokiExtensions['GrandObjectPage']     = array( 'name' => 'GrandObjectPage',
					      'path' => "$IP/extensions/GrandObjectPage/GrandObjectPage.php",
					      'enabled' => true,
					      );
					      
$egAnnokiExtensions['Cache']     = array( 'name' => 'Cache',
					      'path' => "$IP/extensions/Cache/Cache.php",
					      'enabled' => true,
					      );

$egAnnokiExtensions['Cal']           = array( 'name' => 'Calendar',
					      'path' => "$IP/extensions/Calendar/calendar_extension.php",
					      'enabled' => true,
					      );

$egAnnokiExtensions['Vis']           = array( 'name' => 'Visualizations',
					      'path' => "$IP/extensions/Vis/Vis.php",
					      'enabled' => false,
					      );

$egAnnokiExtensions['TempEd']        = array( 'name' => 'Template Editor',
					      'path' => "$IP/extensions/TemplateEditor/TemplateEditor.php",
					      'enabled' => true,
					      );

$egAnnokiExtensions['TextReplace']   = array( 'name' => 'Text Replace',
					      'path' => "$IP/extensions/TextReplace/TextReplace.php",
					      'enabled' => true,
					      );

$egAnnokiExtensions['ProjectPie']     = array( 'name' => 'ProjectPie',
					      'path' => "$IP/extensions/ProjectPie/projectPie.body.php",
					      'enabled' => false,
					      );
					      
$egAnnokiExtensions['ReaSoN']     = array( 'name' => 'ReaSoN',
					      'path' => "$IP/extensions/ReaSoN/ReaSoN.body.php",
					      'enabled' => false,
					      );
					      
$egAnnokiExtensions['Twitter']     = array( 'name' => 'Twitter',
					      'path' => "$IP/extensions/Twitter/Twitter.body.php",
					      'enabled' => true,
					      );
					      
$egAnnokiExtensions['MailingList']     = array( 'name' => 'MailingList',
					      'path' => "$IP/extensions/MailingList/mailingList.body.php",
					      'enabled' => true,
					      );
					      
$egAnnokiExtensions['IndexTables']     = array( 'name' => 'IndexTables',
					      'path' => "$IP/extensions/IndexTables/IndexTable.body.php",
					      'enabled' => true,
					      );
					      
$egAnnokiExtensions['FeatureRequest']     = array( 'name' => 'FeatureRequest',
					      'path' => "$IP/extensions/FeatureRequest/FeatureRequest.body.php",
					      'enabled' => true,
					      );
					      
$egAnnokiExtensions['GoogleAlertReader']     = array( 'name' => 'GoogleAlertReader',
					      'path' => "$IP/extensions/GoogleAlertReader/GoogleAlertReader.body.php",
					      'enabled' => true,
					      );
					      
$egAnnokiExtensions['SociQL']     = array( 'name' => 'SociQL Queries',
					      'path' => "$IP/extensions/SociQL/Queries.php",
					      'enabled' => true,
					      );
					      
$egAnnokiExtensions['VQE']     = array( 'name' => 'Visual Query Editor',
					      'path' => "$IP/extensions/VisualQueryEditor/VQE.php",
					      'enabled' => true,
					      );
					      
$egAnnokiExtensions['SociQLMaintenance']     = array( 'name' => 'SociQL Maintenance',
					      'path' => "$IP/extensions/MaintenanceService/SociQLMaintenance.php",
					      'enabled' => false,
					      );
					      
$egAnnokiExtensions['AddMember']     = array( 'name' => 'AddMember',
					      'path' => "$IP/extensions/AddMember/AddMember.body.php",
					      'enabled' => true,
					      );
					      
$egAnnokiExtensions['EditMember']     = array( 'name' => 'EditMember',
					      'path' => "$IP/extensions/EditMember/EditMember.php",
					      'enabled' => true,
					      );
					      
$egAnnokiExtensions['API']     = array( 'name' => 'API',
					      'path' => "$IP/extensions/API/API.body.php",
					      'enabled' => true,
					      );
					      
$egAnnokiExtensions['ImportBibTex']     = array( 'name' => 'Import BibTex',
					      'path' => "$IP/extensions/ImportBibTex/ImportBibTex.body.php",
					      'enabled' => true,
					      );
					      
$egAnnokiExtensions['Poll']     = array( 'name' => 'Poll',
					      'path' => "$IP/extensions/Poll/Poll.body.php",
					      'enabled' => true,
					      );
					      
$egAnnokiExtensions['PDFGenerator']     = array( 'name' => 'PDF Generator',
					      'path' => "$IP/extensions/PDFGenerator/PDFGenerator.php",
					      'enabled' => true,
					      );
					      
$egAnnokiExtensions['Report']     = array( 'name' => 'Report',
					      'path' => "$IP/extensions/Report/AbstractReport.php",
					      'enabled' => true,
					      );
					      					      
$egAnnokiExtensions['ReportTables']     = array( 'name' => 'ReportTables',
					      'path' => "$IP/extensions/ReportTables/Report.php",
					      'enabled' => true,
					      );
$egAnnokiExtensions['ReportStats']     = array( 'name' => 'ReportStats',
					      'path' => "$IP/extensions/ReportStats/ReportStats.php",
					      'enabled' => true,
					      );
					      
$egAnnokiExtensions['ProjectMilestones']     = array( 'name' => 'Project Milestones',
					      'path' => "$IP/extensions/ProjectMilestones/ProjectMilestones.php",
					      'enabled' => true,
					      );

$egAnnokiExtensions['SessionData']     = array( 'name' => 'Session Data',
					      'path' => "$IP/extensions/SessionData/SessionData.php",
					      'enabled' => true,
					      );
					      
$egAnnokiExtensions['QueryableTable']     = array( 'name' => 'Queryable Table',
					      'path' => "$IP/extensions/QueryableTable/QueryableTable.php",
					      'enabled' => true,
					      );
					      
$egAnnokiExtensions['OutputArticleText']     = array( 'name' => 'OutputArticleText',
					      'path' => "$IP/extensions/OutputArticleText/OutputArticleText.php",
					      'enabled' => true,
					      );

$egAnnokiExtensions['CreatePDF']     = array( 'name' => 'Create PDF Tool',
					      'path' => "$IP/extensions/CreatePDF/CreatePDF.php",
					      'enabled' => true,
					      );

$egAnnokiExtensions['EvaluationTable']     = array( 'name' => 'Evaluation Table',
					      'path' => "$IP/extensions/ReportTables/EvaluationTable.php",
					      'enabled' => true,
					      );

$egAnnokiExtensions['Postings']     = array( 'name' => 'GRAND Postings',
					      'path' => "$IP/extensions/Postings/Postings.php",
					      'enabled' => true,
					      );
					      
$egAnnokiExtensions['EmptyEmailList']     = array( 'name' => 'Empty Email List',
					      'path' => "$IP/extensions/EmptyEmailList/EmptyEmailList.php",
					      'enabled' => true,
					      );
					      
$egAnnokiExtensions['ReportArchive']     = array( 'name' => 'Report Archive',
					      'path' => "$IP/extensions/ReportArchive/ReportArchive.php",
					      'enabled' => true,
					      );
					      
$egAnnokiExtensions['Search']     = array( 'name' => 'Search',
					      'path' => "$IP/extensions/Search/Search.php",
					      'enabled' => true,
					      );
					      
$egAnnokiExtensions['Impersonation']     = array( 'name' => 'Impersonation',
					      'path' => "$IP/extensions/Impersonation/Impersonate.php",
					      'enabled' => true,
					      );
					      
$egAnnokiExtensions['Visualisations']     = array( 'name' => 'Visualisations',
					      'path' => "$IP/extensions/Visualisations/Visualisation.php",
					      'enabled' => true,
					      );
					      
$egAnnokiExtensions['Survey']     = array( 'name' => 'Survey',
					      'path' => "$IP/extensions/Survey/Survey.php",
					      'enabled' => true,
					      );
					      
$egAnnokiExtensions['Duplicates']     = array( 'name' => 'Duplicates',
					      'path' => "$IP/extensions/Duplicates/Duplicates.php",
					      'enabled' => true,
					      );
					      
$egAnnokiExtensions['Acknowledgements']     = array( 'name' => 'Acknowledgements',
					      'path' => "$IP/extensions/Acknowledgements/Acknowledgements.php",
					      'enabled' => true,
					      );
					      
$egAnnokiExtensions['AllocatedBudgets']     = array( 'name' => 'Allocated Budgets',
					      'path' => "$IP/extensions/AllocatedBudgets/AllocatedBudgets.php",
					      'enabled' => true,
					      );
					      
$egAnnokiExtensions['ProjectEvolution']     = array( 'name' => 'Project Evolution',
					      'path' => "$IP/extensions/ProjectEvolution/ProjectEvolution.php",
					      'enabled' => true,
					      );
					      
$egAnnokiExtensions['ReviewerConflicts']     = array( 'name' => 'Reviewer Conflicts',
					      'path' => "$IP/extensions/ReviewerConflicts/ReviewerConflicts.php",
					      'enabled' => true,
					      );
					      
$egAnnokiExtensions['ReportPDFs']     = array( 'name' => 'ReportPDFs',
					      'path' => "$IP/extensions/Report/SpecialPages/ReportPDFs.php",
					      'enabled' => true,
					      );
					      
$egAnnokiExtensions['ScreenCapture']     = array( 'name' => 'ScreenCapture',
					      'path' => "$IP/extensions/ScreenCapture/ScreenCapture.php",
					      'enabled' => true,
					      );
					      
$egAnnokiExtensions['Solr']     = array( 'name' => 'Solr',
					      'path' => "$IP/extensions/Solr/Solr.php",
					      'enabled' => true,
					      );
					      
$egAnnokiExtensions['AcademiaMap']     = array( 'name' => 'AcademiaMap',
					      'path' => "$IP/extensions/AcademiaMap/AcademiaMap.php",
					      'enabled' => true,
					      );

$egAnnokiExtensions['TravelForm']     = array( 'name' => 'TravelForm',
					      'path' => "$IP/extensions/TravelForm/TravelForm.php",
					      'enabled' => true,
					      );

/** Install all enumerated Annoki-based extensions **/
foreach($egAnnokiExtensions as $key => $extension){
    if ($extension['enabled'] && is_readable($extension['path'])){
        $start = microtime(true);
        $mem_before = memory_get_usage();
        require_once($extension['path']);
        $mem_after = memory_get_usage();
        $end = microtime(true);
        $egAnnokiExtensions[$key]['size'] = number_format(($mem_after - $mem_before)/1024/1024, 2);
        $egAnnokiExtensions[$key]['time'] = number_format(($end - $start)*1000, 2);
    }
    else {
        $egAnnokiExtensions[$key]['size'] = "0.00";
        $egAnnokiExtensions[$key]['time'] = "0.00";
    }
}

$dir = dirname(__FILE__) . '/';
 
$wgAutoloadClasses['AnnokiControl'] = $dir . 'AnnokiControl_body.php'; # Tell MediaWiki to load the extension body.
$wgExtensionMessagesFiles['AnnokiControl'] = $dir . 'AnnokiControl.i18n.php';
$wgSpecialPages['AnnokiControl'] = 'AnnokiControl'; # Let MediaWiki know about the special page.
$wgHooks['LanguageGetSpecialPageAliases'][] = 'AnnokiControl::setLocalizedPageName'; # Add any aliases for the special page.
$wgHooks['BeforePageDisplay'][] = 'AnnokiControl::addCustomJavascript';

$wgExtensionCredits['specialpage'][] = array(
					     'name' => 'AnnokiControl',
					     'author' =>'UofA: SERL',
					     //'url' => 'http://www.mediawiki.org/wiki/User:JDoe',
					     'description' => 'Manages installation and configuration of other Annoki extensions.'
					     );
					     
function getTableName($baseName) {
	$dbr = wfGetDB(DB_READ);
	$tblName = $dbr->tableName("$baseName");
	$tblName = str_replace("`", "", "$tblName");
	return $tblName;
}

$wgHooks['SpecialPage_initList'][] = 'orderSpecialPages';
function orderSpecialPages(&$aSpecialPages){
    $array1 = array();
    $array2 = array();
    $skip = false;
    foreach($aSpecialPages as $key => $page){
        if($skip == true){
            $array1[$key] = $page;
        }
        else{
            $array2[$key] = $page;
        }
        if($key == "Invalidateemail"){
            $skip = true;
        }
    }
    $aSpecialPages = array_merge($array1, $array2);
    return true;
}

?>
