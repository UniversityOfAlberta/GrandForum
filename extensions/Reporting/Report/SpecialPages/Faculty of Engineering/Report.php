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
                $tabs["Rebuttal"] = TabUtils::createTab("Rebuttal");
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
            if($person->faculty == getFaculty()){
                if($person->isRole(NI) || $person->isRole("ATS")){
                    $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "Rebuttal")) ? "selected" : false;
                    $tabs["Rebuttal"]['subtabs'][] = TabUtils::createSubTab("Rebuttal", "{$url}Rebuttal", $selected);
                }
            }
            
            // FEC Table
            if($person->faculty == getFaculty() || $person->isRole("FEC ".getFaculty())){
                if($person->isRole(ADMIN) || $person->isRole(DEAN) || $person->isRole(VDEAN) || $person->isRole(HR) || $person->isRole("FEC") || $person->isRole("FEC ".getFaculty())){
                    $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "FECTable")) ? "selected" : false;
                    $tabs["FEC"]['subtabs'][] = TabUtils::createSubTab("Annual Reports", "{$url}FECTable", $selected);
                }
            }
        }
        return true;
    }
}

?>
