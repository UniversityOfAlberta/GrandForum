<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['Descriptors612Month'] = 'Descriptors612Month'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['Descriptors612Month'] = $dir . 'Descriptors612Month.i18n.php';
$wgSpecialPageGroups['Descriptors612Month'] = 'reporting-tools';

$wgHooks['SubLevelTabs'][] = 'Descriptors612Month::createSubTabs';

function runDescriptors612Month($par) {
    Descriptors612Month::execute($par);
}

class Descriptors612Month extends Descriptors {
    
    static function getPeople(){
        $allPeople = Person::getAllPeople(CI);
        $people = array();
        foreach($allPeople as $person){
            if(AVOIDDashboard::hasSubmittedSurvey($person->getId(), "RP_AVOID_SIXMO") &&
               AVOIDDashboard::hasSubmittedSurvey($person->getId(), "RP_AVOID_TWELVEMO")){
                $people[] = $person;
            }
        }
        return $people;
    }
    
    function __construct(){
        SpecialPage::__construct("Descriptors612Month", null, true, 'runDescriptors612Month');
    }
    
    function execute($par){
        parent::execute($par);
        $this->getOutput()->setPageTitle("6 and 12 Month Descriptives");
    }
    
    static function createSubTabs(&$tabs){
        global $wgUser, $wgServer, $wgScriptPath, $wgTitle, $config;
        $person = Person::newFromWgUser();
        if($person->isRoleAtLeast(STAFF) && $config->getValue('networkFullName') != "AVOID Australia"){
            $selected = @($wgTitle->getText() == "Descriptors612Month") ? "selected" : false;
            $tabs['Manager']['subtabs']['descriptives']['dropdown'][] = TabUtils::createSubTab("6 & 12 Month", "{$wgServer}{$wgScriptPath}/index.php/Special:Descriptors612Month", $selected);
        }
        return true;
    }
    
}

?>
