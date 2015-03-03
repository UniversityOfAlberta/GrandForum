<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['ReportPDFs'] = 'ReportPDFs'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['ReportPDFs'] = $dir . 'ReportPDFs.i18n.php';
$wgSpecialPageGroups['ReportPDFs'] = 'reporting-tools';

$wgHooks['TopLevelTabs'][] = 'ReportPDFs::createTab';
$wgHooks['SubLevelTabs'][] = 'ReportPDFs::createSubTabs';

class ReportPDFs extends AbstractReport{
    
    function ReportPDFs(){
        global $config;
        $report = @$_GET['report'];
        
        $this->AbstractReport(dirname(__FILE__)."/../../ReportXML/{$config->getValue('networkName')}/$report.xml", -1, false, false);
        wfLoadExtensionMessages("ReportPDFs");
        SpecialPage::SpecialPage("ReportPDFs", null, true);
    }
    
    function userCanExecute($user){
        $me = Person::newFromWgUser();
        if($me->isRole(NCE) ||
           $me->isRoleAtLeast(MANAGER)){
            return true;
        }
        return false;
    }

    static function createTab(&$tabs){
		global $wgServer, $wgScriptPath, $wgUser, $wgTitle;
		$page = "ReportPDFs?report=PDFMaterials";
		$tabs["Report PDFs"] = TabUtils::createTab("Report PDFs");
        return true;
	}
	
	static function createSubTabs(&$tabs){
	    global $wgServer, $wgScriptPath, $wgUser, $wgTitle;
	    if(!$wgUser->isLoggedIn() || !self::userCanExecute($wgUser)){
            return true;
        }
        $selected = ($wgTitle->getText() == "ReportPDFs" && $_GET['report'] == "PDFMaterials") ? "selected" : false;
        $tabs["Report PDFs"]['subtabs'][] = TabUtils::createSubTab("PDF Materials", "$wgServer$wgScriptPath/index.php/Special:ReportPDFs?report=PDFMaterials", $selected);
        return true;
	}
}

?>
