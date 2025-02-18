<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['Descriptors6Month'] = 'Descriptors6Month'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['Descriptors6Month'] = $dir . 'Descriptors6Month.i18n.php';
$wgSpecialPageGroups['Descriptors6Month'] = 'reporting-tools';

$wgHooks['SubLevelTabs'][] = 'Descriptors6Month::createSubTabs';

function runDescriptors12Month($par) {
    Descriptors12Month::execute($par);
}

class Descriptors6Month extends Descriptors {
    
    static function getPeople(){
        $allPeople = Person::getAllPeople(CI);
        $people = array();
        foreach($allPeople as $person){
            if(AVOIDDashboard::hasSubmittedSurvey($person->getId(), "RP_AVOID_SIXMO")){
                $people[] = $person;
            }
        }
        return $people;
    }
    
    function __construct(){
        SpecialPage::__construct("Descriptors6Month", null, true, 'runDescriptors6Month');
    }
    
    static function createSubTabs(&$tabs){
        global $wgUser, $wgServer, $wgScriptPath, $wgTitle, $config;
        $person = Person::newFromWgUser();
        if($person->isRoleAtLeast(STAFF) && $config->getValue('networkFullName') != "AVOID Australia"){
            $selected = @($wgTitle->getText() == "Descriptors6Month") ? "selected" : false;
            $tabs['Manager']['subtabs'][] = TabUtils::createSubTab("Descriptives 6 Month", "{$wgServer}{$wgScriptPath}/index.php/Special:Descriptors6Month", $selected);
        }
        return true;
    }
    
}

?>
