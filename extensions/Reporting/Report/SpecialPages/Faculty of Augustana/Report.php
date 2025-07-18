<?php

class Report extends TemplateReport{
    
    function __construct(){
        parent::__construct();
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
            if($person->isRole(DEAN) || $person->isRole(VDEAN) || $person->isRole(HR) || $person->isRole("ATSEC")){
                $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "ATSECTable")) ? "selected" : false;
                $tabs["ATSEC"]['subtabs'][] = TabUtils::createSubTab("Annual Reports", "{$url}ATSECTable", $selected);
            }
        }
        return true;
    }
}

?>
