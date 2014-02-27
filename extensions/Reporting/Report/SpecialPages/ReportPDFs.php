<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['ReportPDFs'] = 'ReportPDFs'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['ReportPDFs'] = $dir . 'ReportPDFs.i18n.php';
$wgSpecialPageGroups['ReportPDFs'] = 'reporting-tools';

$wgHooks['SkinTemplateContentActions'][] = 'ReportPDFs::showTabs';
$wgHooks['TopLevelTabs'][] = 'ReportPDFs::createTab';

class ReportPDFs extends AbstractReport{
    
    function ReportPDFs(){
        $report = @$_GET['report'];
        
        $this->AbstractReport(dirname(__FILE__)."/../ReportXML/$report.xml", -1, false, false);
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

    static function createTab($tabs){
		global $wgServer, $wgScriptPath, $wgUser, $wgTitle;
		if(!$wgUser->isLoggedIn() || !self::userCanExecute($wgUser)){
            return true;
        }
        $person = Person::newFromWgUser();
		$page = "ReportPDFs?report=PDFMaterials";
		
		$selected = "";
		if($wgTitle->getText() == "ReportPDFs"){
		    $selected = "selected";
		}
		$tabs["Report PDFs"] = array('id' => "lnk-my_report",
                                    'href' => "$wgServer$wgScriptPath/index.php/Special:$page", 
                                    'text' => "Report PDFs", 
                                    'selected' => $selected);
        return true;
	}
    static function showTabs(&$content_actions){ return true; }

    /*static function showTabs(&$content_actions){
        global $wgTitle, $wgUser, $wgServer, $wgScriptPath;
        if($wgTitle->getText() == "Report"){
            $content_actions = array();
            $person = Person::newFromId($wgUser->getId());
            
            // Individual Report
            if($person->isRoleAtLeast(PNI)){
                $class = @($wgTitle->getText() == "Report" && ($_GET['report'] == "PDFMaterials")) ? "selected" : false;
                $text = "PDFMaterials";
                $content_actions[] = array (
                         'class' => $class,
                         'text'  => $text,
                         'href'  => "$wgServer$wgScriptPath/index.php/Special:Report?report=PDFMaterials",
                        );
            }
            
            
        }
        return true;
    }*/
}

?>
