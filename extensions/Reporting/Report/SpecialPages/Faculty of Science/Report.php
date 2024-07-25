<?php

class Report extends TemplateReport{
    
    function Report(){
        parent::TemplateReport();
    }
    
    static function createTab(&$tabs){
        parent::createTab($tabs);
        $person = Person::newFromWgUser();
        if($person->isLoggedIn() && $person instanceof FullPerson){
            $person->getFecPersonalInfo();
            if($person->faculty == getFaculty()){
                $tabs["Sabbatical"] = TabUtils::createTab("Sabbatical Application");
            }
        }
        return true;
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle;
        parent::createSubTabs($tabs);
        $person = Person::newFromWgUser();
        if($person->isLoggedIn() && $person instanceof FullPerson){
            $person->getFecPersonalInfo();
            if($person->faculty == getFaculty()){
                $url = "$wgServer$wgScriptPath/index.php/Special:Report?report=";
                if($person->isRole(NI) || $person->isRole("ATS")){
                    $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "SabbaticalApplication")) ? "selected" : false;
                    $tabs["Sabbatical"]['subtabs'][] = TabUtils::createSubTab("Sabbatical Application", "{$url}SabbaticalApplication", $selected);
                }
            }
        }
        return true;
    }
}

?>
