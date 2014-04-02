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

function isExtensionEnabled($ext){
    global $config;
    $extensions = $config->getValue('extensions');
    return (array_search($ext, $extensions) !== false);
}

$egAnnokiExtensions = array();

$egAnnokiExtensions['AccessControl'] = array('name' => 'Annoki Access Controls',
                                             'path' => "$IP/extensions/AccessControls/AccessControls.php");

$egAnnokiExtensions['Cache'] = array('name' => 'Cache',
                                     'path' => "$IP/extensions/Cache/Cache.php");

$egAnnokiExtensions['Messages'] = array('name' => 'Messages',
                                        'path' => "$IP/extensions/Messages/Message.php");

$egAnnokiExtensions['TabUtils'] = array('name' => 'TabUtils',
                                        'path' => "$IP/extensions/TabUtils/TabUtils.php");

$egAnnokiExtensions['API'] = array('name' => 'API',
                                   'path' => "$IP/extensions/API/API.body.php");

$egAnnokiExtensions['GrandObjects'] = array('name' => 'GrandObjects',
                                            'path' => "$IP/extensions/GrandObjects/GrandObjects.php");

$egAnnokiExtensions['UI'] = array('name' => 'User Interface',
                                  'path' => "$IP/extensions/UI/UIElement.php");

$egAnnokiExtensions['Notification'] = array('name' => 'Notification',
                                            'path' => "$IP/extensions/Notification/Notification.body.php");

$egAnnokiExtensions['GrandObjectPage'] = array('name' => 'GrandObjectPage',
                                               'path' => "$IP/extensions/GrandObjectPage/GrandObjectPage.php");

$egAnnokiExtensions['IndexTables'] = array( 'name' => 'IndexTables',
                                            'path' => "$IP/extensions/IndexTables/IndexTable.body.php");

$egAnnokiExtensions['Cal'] = array('name' => 'Calendar',
                                   'path' => "$IP/extensions/Calendar/calendar_extension.php");

$egAnnokiExtensions['TempEd'] = array('name' => 'Template Editor',
                                      'path' => "$IP/extensions/TemplateEditor/TemplateEditor.php");

$egAnnokiExtensions['TextReplace'] = array('name' => 'Text Replace',
                                           'path' => "$IP/extensions/TextReplace/TextReplace.php");

$egAnnokiExtensions['Twitter'] = array('name' => 'Twitter',
                                       'path' => "$IP/extensions/Twitter/Twitter.body.php");

$egAnnokiExtensions['MailingList'] = array('name' => 'MailingList',
                                           'path' => "$IP/extensions/MailingList/mailingList.body.php");

$egAnnokiExtensions['FeatureRequest'] = array('name' => 'FeatureRequest',
                                              'path' => "$IP/extensions/FeatureRequest/FeatureRequest.body.php");

$egAnnokiExtensions['SociQL'] = array('name' => 'SociQL Queries',
                                      'path' => "$IP/extensions/SociQL/Queries.php");

$egAnnokiExtensions['VQE'] = array('name' => 'Visual Query Editor',
                                   'path' => "$IP/extensions/VisualQueryEditor/VQE.php");

$egAnnokiExtensions['SociQLMaintenance'] = array('name' => 'SociQL Maintenance',
                                                 'path' => "$IP/extensions/MaintenanceService/SociQLMaintenance.php");

$egAnnokiExtensions['AddMember'] = array('name' => 'AddMember',
                                         'path' => "$IP/extensions/AddMember/AddMember.body.php");

$egAnnokiExtensions['EditMember'] = array('name' => 'EditMember',
                                          'path' => "$IP/extensions/EditMember/EditMember.php");

$egAnnokiExtensions['ImportBibTex'] = array('name' => 'Import BibTex',
                                            'path' => "$IP/extensions/ImportBibTex/ImportBibTex.body.php");

$egAnnokiExtensions['Poll'] = array('name' => 'Poll',
                                    'path' => "$IP/extensions/Poll/Poll.body.php");

$egAnnokiExtensions['QueryableTable'] = array('name' => 'Queryable Table',
                                              'path' => "$IP/extensions/QueryableTable/QueryableTable.php");

$egAnnokiExtensions['Reporting'] = array('name' => 'Reporting',
                                         'path' => "$IP/extensions/Reporting/Reporting.php");

$egAnnokiExtensions['EmptyEmailList'] = array('name' => 'Empty Email List',
                                              'path' => "$IP/extensions/EmptyEmailList/EmptyEmailList.php");

$egAnnokiExtensions['GlobalSearch'] = array('name' => 'Global Search',
                                            'path' => "$IP/extensions/GlobalSearch/GlobalSearch.php");

$egAnnokiExtensions['Impersonation'] = array('name' => 'Impersonation',
                                             'path' => "$IP/extensions/Impersonation/Impersonate.php");

$egAnnokiExtensions['Visualisations'] = array('name' => 'Visualisations',
                                              'path' => "$IP/extensions/Visualisations/Visualisation.php");

$egAnnokiExtensions['Survey'] = array('name' => 'Survey',
                                      'path' => "$IP/extensions/Survey/Survey.php");

$egAnnokiExtensions['Duplicates'] = array('name' => 'Duplicates',
                                          'path' => "$IP/extensions/Duplicates/Duplicates.php");

$egAnnokiExtensions['Acknowledgements'] = array('name' => 'Acknowledgements',
                                                'path' => "$IP/extensions/Acknowledgements/Acknowledgements.php");

$egAnnokiExtensions['AllocatedBudgets'] = array('name' => 'Allocated Budgets',
                                                'path' => "$IP/extensions/AllocatedBudgets/AllocatedBudgets.php");

$egAnnokiExtensions['ProjectEvolution'] = array('name' => 'Project Evolution',
                                                'path' => "$IP/extensions/ProjectEvolution/ProjectEvolution.php");

$egAnnokiExtensions['ScreenCapture'] = array('name' => 'ScreenCapture',
                                             'path' => "$IP/extensions/ScreenCapture/ScreenCapture.php");

$egAnnokiExtensions['Solr'] = array('name' => 'Solr',
                                    'path' => "$IP/extensions/Solr/Solr.php");

$egAnnokiExtensions['AcademiaMap'] = array('name' => 'AcademiaMap',
                                           'path' => "$IP/extensions/AcademiaMap/AcademiaMap.php");

$egAnnokiExtensions['TravelForm'] = array('name' => 'TravelForm',
                                          'path' => "$IP/extensions/TravelForm/TravelForm.php");

$egAnnokiExtensions['EthicsTable'] = array('name' => 'EthicsTable',
                                           'path' => "$IP/extensions/EthicsTable/EthicsTable.php");

$egAnnokiExtensions['AdvancedSearch'] = array('name' => 'AdvancedSearch',
                                              'path' => "$IP/extensions/AdvancedSearch/AdvancedSearch.php");

$egAnnokiExtensions['CCVExport'] = array('name' => 'CCVExport', 
                                         'path' => "$IP/extensions/CCVExport/CCVExport.php");


/** Install all enumerated Annoki-based extensions **/
foreach($egAnnokiExtensions as $key => $extension){
    if (isExtensionEnabled($key) && is_readable($extension['path'])){
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

function debug($message, $type=E_USER_NOTICE){
    if(DEBUG){
        trigger_error($message, $type);
    }
}

?>
