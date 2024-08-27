<?php
if (!defined('MEDIAWIKI')) {
  echo "This file is a MediaWiki extension, and cannot be accessed independantly.";
  exit( 1 );
}

define('ANNOKI', true);

require_once("UnknownAction.php");
require_once("DBFunctions.php");
require_once('AnnokiConfig.php');

/** Enumerate Annoki-based extensions.  Add new ones in a similar fashion. Example:
$egAnnokiExtensions['MyAnnokiExtension'] = array( 'name' => 'My Annoki Extension', //This show up in the Annoki extension list
                              'path' => "$IP/extensions/MyAnnokiExtension/MyAnnokiExtension.php", //The path to the main extension php file.
                              'enabled' => true, //True to use the extension, false otherwise.
                              );
**/
function autoload_register($directory){
    spl_autoload_register(function ($class) use ($directory) {
        $exploded = explode("\\", $class);
        $class = $exploded[count($exploded)-1]; 
        if(file_exists(dirname(__FILE__) . "/../$directory/" . $class . '.php')){
            require_once(dirname(__FILE__) . "/../$directory/" . $class . '.php');
        }
    });
}

function redirect($url){
    session_write_close();
    header("Location: $url");
    DBFunctions::commit();
    exit;
}

function isExtensionEnabled($ext){
    global $config;
    $extensions = $config->getValue('extensions');
    return (array_search($ext, $extensions) !== false);
}

autoload_register('../Classes/Inflect');

$egAnnokiExtensions = array();

$egAnnokiExtensions['Shibboleth'] = array('name' => 'Shibboleth',
                                             'path' => "$IP/extensions/Shibboleth/Shibboleth.php");
                                             
$egAnnokiExtensions['GoogleLogin'] = array('name' => 'GoogleLogin',
                                             'path' => "$IP/extensions/GoogleLogin/GoogleLogin.body.php");

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

$egAnnokiExtensions['MailingList'] = array('name' => 'MailingList',
                                           'path' => "$IP/extensions/MailingList/mailingList.body.php");

$egAnnokiExtensions['AddMember'] = array('name' => 'AddMember',
                                         'path' => "$IP/extensions/AddMember/AddMember.body.php");
                                          
$egAnnokiExtensions['Register'] = array('name' => 'Register',
                                        'path' => "$IP/extensions/Register/Register.php");

$egAnnokiExtensions['Poll'] = array('name' => 'Poll',
                                    'path' => "$IP/extensions/Poll/Poll.body.php");

$egAnnokiExtensions['QueryableTable'] = array('name' => 'Queryable Table',
                                              'path' => "$IP/extensions/QueryableTable/QueryableTable.php");

$egAnnokiExtensions['NCETable'] = array('name' => 'NCETable',
                                        'path' => "$IP/extensions/NCETable/NCETable.php");

$egAnnokiExtensions['Reporting'] = array('name' => 'Reporting',
                                         'path' => "$IP/extensions/Reporting/Reporting.php");
                                         
$egAnnokiExtensions['DiversitySurvey'] = array('name' => 'DiversitySurvey',
                                         'path' => "$IP/extensions/DiversitySurvey/DiversitySurvey.php");

$egAnnokiExtensions['GlobalSearch'] = array('name' => 'Global Search',
                                            'path' => "$IP/extensions/GlobalSearch/GlobalSearch.php");

$egAnnokiExtensions['Impersonation'] = array('name' => 'Impersonation',
                                             'path' => "$IP/extensions/Impersonation/Impersonate.php");

$egAnnokiExtensions['Visualizations'] = array('name' => 'Visualizations',
                                              'path' => "$IP/extensions/Visualizations/Visualization.php");
                                              
$egAnnokiExtensions['PublicVisualizations'] = array('name' => 'Public Visualizations',
                                                    'path' => "$IP/extensions/Visualizations/PublicVisualizations/PublicVisualizations.php");

$egAnnokiExtensions['Duplicates'] = array('name' => 'Duplicates',
                                          'path' => "$IP/extensions/Duplicates/Duplicates.php");

$egAnnokiExtensions['ProjectEvolution'] = array('name' => 'Project Evolution',
                                                'path' => "$IP/extensions/ProjectEvolution/ProjectEvolution.php");

$egAnnokiExtensions['CCVExport'] = array('name' => 'CCVExport', 
                                         'path' => "$IP/extensions/CCVExport/CCVExport.php");
                                         
$egAnnokiExtensions['CrossForumExport'] = array('name' => 'CrossForumExport', 
                                                'path' => "$IP/extensions/CrossForumExport/CrossForumExport.php");
                                         
$egAnnokiExtensions['ReportIssue'] = array('name' => 'ReportIssue', 
                                           'path' => "$IP/extensions/ReportIssue/ReportIssue.php");

$egAnnokiExtensions['MyThreads'] = array('name' => 'MyThreads',
                                         'path' => "$IP/extensions/MyThreads/MyThreads.php");
                                         
$egAnnokiExtensions['UofANews'] = array('name' => 'UofANews',
                                        'path' => "$IP/extensions/UofANews/UofANews.php");
                                         
$egAnnokiExtensions['Freeze'] = array('name' => 'Freeze',
                                      'path' => "$IP/extensions/Freeze/Freeze.php");
                                      
$egAnnokiExtensions['RSSAlerts'] = array('name' => 'RSSAlerts',
                                         'path' => "$IP/extensions/RSSAlerts/RSSAlerts.php");
                                         
$egAnnokiExtensions['EventRegistration'] = array('name' => 'EventRegistration',
                                         'path' => "$IP/extensions/EventRegistration/SpecialEventRegistration.php");

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

require_once("$IP/extensions/CrossForumExport/CrossForumExport.php");

require_once("AnnokiControl_body.php");
$wgHooks['MessagesPreLoad'][] = 'AnnokiControl::onMessagesPreLoad';
$wgHooks['UserGetLanguageObject'][] = 'AnnokiControl::onUserGetLanguageObject';

$wgExtensionCredits['specialpage'][] = array(
                         'name' => 'AnnokiControl',
                         'author' =>'UofA: SERL',
                         //'url' => 'http://www.mediawiki.org/wiki/User:JDoe',
                         'description' => 'Manages installation and configuration of other Annoki extensions.'
                         );
                         
function getTableName($baseName) {
    $dbr = wfGetDB(DB_REPLICA);
    $tblName = $dbr->tableName("$baseName");
    $tblName = str_replace("`", "", "$tblName");
    return $tblName;
}

$wgHooks['SpecialPage_initList'][] = 'orderSpecialPages';
function orderSpecialPages(&$aSpecialPages){
    $me = Person::newFromWgUser();
    $array1 = array();
    $array2 = array();
    $skip = false;
    foreach($aSpecialPages as $key => $page){
        //echo "$key\n";
        if(!$me->isRoleAtLeast(STAFF) && 
            ($key == "Log" || $key == "Listusers" ||
             $key == "Listgrouprights" ||
             $key == "BlockList" || $key == "Activeusers" || 
             $key == "Allmessages" || $key == "Statistics" ||
             $key == "Version" || $key == "Recentchanges" ||
             $key == "Recentchangeslinked" || $key == "Tags" ||
             $key == "CreateAccount")){
            unset($aSpecialPages[$key]);
            continue;
        }
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
