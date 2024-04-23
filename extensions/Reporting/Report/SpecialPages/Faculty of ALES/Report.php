<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['Report'] = 'Report'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['Report'] = $dir . 'Report.i18n.php';
$wgSpecialPageGroups['Report'] = 'reporting-tools';

$wgHooks['TopLevelTabs'][] = 'Report::createTab';
$wgHooks['SubLevelTabs'][] = 'Report::createSubTabs';

class Report extends AbstractReport{
    
    function Report(){
        global $config;
        $report = @$_GET['report'];
        $topProjectOnly = false;
        $this->AbstractReport(dirname(__FILE__)."/../../ReportXML/{$config->getValue('networkName')}/$report.xml", -1, false, $topProjectOnly);
    }

    static function createTab(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        if($wgUser->isLoggedIn()){
            $tabs["Reports"] = TabUtils::createTab("My Annual Report");
            $tabs["ReportArchive"] = TabUtils::createTab("Report Archive");
            $tabs["Chair"] = TabUtils::createTab("Chair");
            $tabs["Dean"] = TabUtils::createTab("Dean");
            $tabs["FEC"] = TabUtils::createTab("FEC");
            $tabs["ATSEC"] = TabUtils::createTab("ATSEC");
            $tabs["CV"] = TabUtils::createTab("My QA CV");
        }
        return true;
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle;
        $person = Person::newFromWgUser();
        $url = "$wgServer$wgScriptPath/index.php/Special:Report?report=";
        if($person->isRole(NI) || $person->isRole("ATS")){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "FEC")) ? "selected" : false;
            $tabs["Reports"]['subtabs'][] = TabUtils::createSubTab("Annual Report", "{$url}FEC", $selected);
            
            //$selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "QACV")) ? "selected" : false;
            //$tabs["CV"]['subtabs'][] = TabUtils::createSubTab("QA CV", "{$url}QACV", $selected);
        }

        if($person->isRole(CHAIR) || $person->isRoleDuring(CHAIR, REPORTING_CYCLE_START, REPORTING_CYCLE_END) || $person->isRole(EA) || $person->isRole(ACHAIR)){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "ChairTable")) ? "selected" : false;
            $tabs["Chair"]['subtabs'][] = TabUtils::createSubTab("Annual Reports", "{$url}ChairTable", $selected);
        }
        
        if($person->isRole(DEAN) || $person->isRoleDuring(DEAN, REPORTING_CYCLE_START, REPORTING_CYCLE_END) || $person->isRole(DEANEA)){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "ChairTable")) ? "selected" : false;
            $tabs["Dean"]['subtabs'][] = TabUtils::createSubTab("Annual Reports", "{$url}ChairTable", $selected);
        }
        
        if($person->isRole(DEAN) || $person->isRole(VDEAN) || $person->isRole(HR) || $person->isRole(FEC)){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "FECTable")) ? "selected" : false;
            $tabs["FEC"]['subtabs'][] = TabUtils::createSubTab("Annual Reports", "{$url}FECTable", $selected);
        }
        if($person->isRole(DEAN) || $person->isRole(VDEAN) || $person->isRole(HR) || $person->isRole(ATSEC)){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "ATSECTable")) ? "selected" : false;
            $tabs["ATSEC"]['subtabs'][] = TabUtils::createSubTab("Annual Reports", "{$url}ATSECTable", $selected);
        }
        
        if($person->isRole(CHAIR) || $person->isRole(EA)){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "DepartmentPublications")) ? "selected" : false;
            $tabs["Chair"]['subtabs'][] = TabUtils::createSubTab("Publications", "{$url}DepartmentPublications", $selected);
        }
        
        if($person->isRole(DEAN) || $person->isRole(DEANEA) || $person->isRole(VDEAN)){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "FacultyPublications")) ? "selected" : false;
            $tabs["Dean"]['subtabs'][] = TabUtils::createSubTab("Publications", "{$url}FacultyPublications", $selected);
        }
        
        if($person->isRole(DEAN) || $person->isRole(DEANEA) || $person->isRole(VDEAN) || $person->isRoleAtLeast(STAFF)){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "CoursesTable")) ? "selected" : false;
            $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("Courses", "{$url}CoursesTable", $selected);
        }
        
        if($person->isRole(DEAN) || $person->isRole(DEANEA) || $person->isRole(VDEAN) || $person->isRoleAtLeast(STAFF)){
            $selected = @($wgTitle->getText() == "Report" && ($_GET['report'] == "Letters/Base")) ? "selected" : false;
            $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("Letters", "{$url}Letters/Base", $selected);
        }
        
        return true;
    }
}

?>
