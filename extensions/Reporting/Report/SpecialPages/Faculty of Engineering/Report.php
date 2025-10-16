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
            if($person->inFaculty()){
                $tabs["Rebuttal"] = TabUtils::createTab("Rebuttal");
                $tabs["Variance"] = TabUtils::createTab("Variance");
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
            $url = "$wgServer$wgScriptPath/index.php/Special:Report?report=";
            if($person->inFaculty()){
                if($person->isRole(NI) || $person->isRole("ATS")){
                    $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "Rebuttal")) ? "selected" : false;
                    $tabs["Rebuttal"]['subtabs'][] = TabUtils::createSubTab("Rebuttal", "{$url}Rebuttal", $selected);
                }
                
                if($person->isRole(NI) || $person->isRole("ATS")){
                    $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "Variance")) ? "selected" : false;
                    $tabs["Variance"]['subtabs'][] = TabUtils::createSubTab("Variance", "{$url}Variance", $selected);
                }
            }
        }
        return true;
    }
}

?>
