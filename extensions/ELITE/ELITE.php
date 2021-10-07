<?php

require_once("ElitePosting.php");
require_once("EliteProfile.php");
require_once("InternEliteProfile.php");
require_once("PhDEliteProfile.php");
require_once("API/ElitePostingAPI.php");
require_once("API/EliteProfileAPI.php");
require_once("ElitePostingPage.php");

$wgHooks['BeforePageDisplay'][] = 'initElitePosting';
$wgHooks['AddNewAccount'][] = 'afterCreateEliteUser';

function initElitePosting($out, $skin){
    global $wgServer, $wgScriptPath, $config;
    $me = Person::newFromWgUser();
    
    BackbonePage::$dirs['elitepostingpage'] = dirname(__FILE__);
    $elitePosting = new ElitePostingPage();
    $elitePosting->loadTemplates();
    $elitePosting->loadModels();
    $elitePosting->loadHelpers();
    $elitePosting->loadViews();
    return true;
}

function afterCreateEliteUser($wgUser, $byEmail=true){
    $_POST['candidate'] = 0;
    return true;
}

global $apiRequest;

$apiRequest->addAction('Hidden','eliteposting', 'ElitePostingAPI');
$apiRequest->addAction('Hidden','eliteposting/deleted', 'ElitePostingAPI');
$apiRequest->addAction('Hidden','eliteposting/current', 'ElitePostingAPI');
$apiRequest->addAction('Hidden','eliteposting/current/:start/:count', 'ElitePostingAPI');
$apiRequest->addAction('Hidden','eliteposting/new/:date', 'ElitePostingAPI');
$apiRequest->addAction('Hidden','eliteposting/new/:date/:start/:count', 'ElitePostingAPI');
$apiRequest->addAction('Hidden','eliteposting/:start/:count', 'ElitePostingAPI');
$apiRequest->addAction('Hidden','eliteposting/:id', 'ElitePostingAPI');
$apiRequest->addAction('Hidden','eliteposting/:id/image/:image_id/:md5', 'ElitePostingAPI');

$apiRequest->addAction('Hidden','eliteprofile/intern', 'EliteProfileAPI');
$apiRequest->addAction('Hidden','eliteprofile/intern/matched', 'EliteProfileAPI');
$apiRequest->addAction('Hidden','eliteprofile/intern/:id', 'EliteProfileAPI');

$apiRequest->addAction('Hidden','eliteprofile/phd', 'EliteProfileAPI');
$apiRequest->addAction('Hidden','eliteprofile/phd/matched', 'EliteProfileAPI');
$apiRequest->addAction('Hidden','eliteprofile/phd/:id', 'EliteProfileAPI');

?>
