<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['ReportPDFs'] = 'ReportPDFs'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['ReportPDFs'] = $dir . 'ReportPDFs.i18n.php';
$wgSpecialPageGroups['ReportPDFs'] = 'reporting-tools';

$wgHooks['SkinTemplateContentActions'][] = 'ReportPDFs::showTabs';

class ReportPDFs extends AbstractReport{
    
    function ReportPDFs(){
        $report = @$_GET['report'];
        
        $this->AbstractReport(dirname(__FILE__)."/../ReportXML/$report.xml", -1, false, false);
        wfLoadExtensionMessages("ReportPDFs");
        SpecialPage::SpecialPage("ReportPDFs", null, true);
    }
    
    function userCanExecute($user){
        $me = Person::newFromWgUser();
        if($me->isRole(EXTERNAL) ||
           $me->isRoleAtLeast(MANAGER)){
            return true;
        }
        return false;
    }

    static function createTab(){
		global $wgServer, $wgScriptPath, $wgUser, $wgTitle;
		$person = Person::newFromId($wgUser->getId());
		$page = "ReportPDFs?report=PDFMaterials";
		
		$selected = "";
		if($wgTitle->getText() == "ReportPDFs"){
		    $selected = "selected";
		}
		
		echo "<li class='top-nav-element $selected'>\n";
		echo "	<span class='top-nav-left'>&nbsp;</span>\n";
		echo "	<a id='lnk-my_report' class='top-nav-mid' href='$wgServer$wgScriptPath/index.php/Special:$page' class='new'>Report PDFs</a>\n";
		echo "	<span class='top-nav-right'>&nbsp;</span>\n";
		echo "</li>";
	}
    static function showTabs(&$content_actions){return true; }

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
