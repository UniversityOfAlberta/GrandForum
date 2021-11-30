<?php

BackbonePage::register('DiversitySurvey', "{$config->getValue('networkName')} Diversity Census Questionnaire", 'network-tools', dirname(__FILE__));
require_once("DiversityStats.php");

$wgHooks['TopLevelTabs'][] = 'DiversitySurvey::createTab';
$wgHooks['SubLevelTabs'][] = 'DiversitySurvey::createSubTabs';

class DiversitySurvey extends BackbonePage {
    
    function isListed(){
        return false;
    }
    
    static function isEligible($person){
        return ($person->isRole(NI) ||
                $person->isRole(HQP) ||
                $person->isRole(STAFF) ||
                $person->isRole("BOD") ||
                $person->isRole("CC") ||
                $person->isRole("ETC") ||
                $person->isRole("RMC") ||
                $person->isRole("SAB") ||
                $person->isRole(EDI));
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return ($person->isLoggedIn() && self::isEligible($person));
    }
    
    function getTemplates(){
        return array('Backbone/*',
                     'diversity_en',
                     'diversity_fr');
    }
    
    function getViews(){
        return array('Backbone/*',
                     'DiversitySurveyView');
    }
    
    function getModels(){
        return array('Backbone/*');
    }
    
    static function createTab(&$tabs){
        $tabs["EDI"] = TabUtils::createTab("EDI");
        return true;
    }

    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgTitle, $wgUser;
        if($wgUser->isLoggedIn()){
            $selected = @($wgTitle->getText() == "EDITraining") ? "selected" : false;
            $tabs["EDI"]['subtabs'][] = TabUtils::createSubTab("Training", "$wgServer$wgScriptPath/index.php/EDITraining", $selected);
        }
        if((new self)->userCanExecute($wgUser)){
            $selected = @($wgTitle->getText() == "DiversitySurvey") ? "selected" : false;
            $tabs["EDI"]['subtabs'][] = TabUtils::createSubTab("Survey", "$wgServer$wgScriptPath/index.php/Special:DiversitySurvey", $selected);
        }
        return true;
    }

}

?>
