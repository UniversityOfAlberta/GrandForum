<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['Descriptors12Month'] = 'Descriptors12Month'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['Descriptors12Month'] = $dir . 'Descriptors12Month.i18n.php';
$wgSpecialPageGroups['Descriptors12Month'] = 'reporting-tools';

$wgHooks['SubLevelTabs'][] = 'Descriptors12Month::createSubTabs';

function runDescriptors12Month($par) {
    Descriptors12Month::execute($par);
}

class Descriptors12Month extends Descriptors {
    
    static function getPeople(){
        $allPeople = Person::getAllPeople(CI);
        $people = array();
        foreach($allPeople as $person){
            if(AVOIDDashboard::hasSubmittedSurvey($person->getId(), "RP_AVOID_TWELVEMO")){
                $people[] = $person;
            }
        }
        return $people;
    }
    
    function __construct(){
        SpecialPage::__construct("Descriptors12Month", null, true, 'runDescriptors12Month');
    }
    
    static function createSubTabs(&$tabs){
        global $wgUser, $wgServer, $wgScriptPath, $wgTitle, $config;
        $person = Person::newFromWgUser();
        if($person->isRoleAtLeast(STAFF) && $config->getValue('networkFullName') != "AVOID Australia"){
            $selected = @($wgTitle->getText() == "Descriptors12Month") ? "selected" : false;
            $tabs['Manager']['subtabs']['descriptives']['dropdown'][] = TabUtils::createSubTab("12 Month", "{$wgServer}{$wgScriptPath}/index.php/Special:Descriptors12Month", $selected);
        }
        return true;
    }
    
}

?>
