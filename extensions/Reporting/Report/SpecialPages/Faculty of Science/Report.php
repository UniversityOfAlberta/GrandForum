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
            if($person->inFaculty()){
                $url = "$wgServer$wgScriptPath/index.php/Special:Report?report=";
                if($person->isRole(NI)){
                    $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "SabbaticalApplication")) ? "selected" : false;
                    $tabs["Sabbatical"]['subtabs'][] = TabUtils::createSubTab("Sabbatical Application", "{$url}SabbaticalApplication", $selected);
                }
            }
            if($person->isRole(CHAIR)){
                $url = "$wgServer$wgScriptPath/index.php/Special:Report?report=";
                if($person->isRole(CHAIR)){
                    $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "Letters/Base")) ? "selected" : false;
                    $tabs["Chair"]['subtabs'][] = TabUtils::createSubTab("Letters", "{$url}Letters/Base", $selected);
                }
            }
            if($person->isRole(FEC_CHAIR) || $person->isRole(HR) || $person->isRole("ATSEC")){
                $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "ATSECTable")) ? "selected" : false;
                $tabs["ATSEC"]['subtabs'][] = TabUtils::createSubTab("Annual Reports", "{$url}ATSECTable", $selected);
            }
            
            // FEC Table
            if($person->isRole("FEC ".getFaculty())){
                if($person->isRole(ADMIN) || $person->isRole(FEC_CHAIR) || $person->isRole(HR) || $person->isRole("FEC") || $person->isRole("FEC ".getFaculty())){
                    $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "FECTable")) ? "selected" : false;
                    $tabs["FEC"]['subtabs'][] = TabUtils::createSubTab("Annual Reports", "{$url}FECTable", $selected);
                }
            }
        }
        return true;
    }
}

?>
