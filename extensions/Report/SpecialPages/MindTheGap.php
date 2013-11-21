<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['MindTheGap'] = 'MindTheGap'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['MindTheGap'] = $dir . 'MindTheGap.i18n.php';
$wgSpecialPageGroups['MindTheGap'] = 'reporting-tools';

$wgHooks['SkinTemplateContentActions'][] = 'MindTheGap::showTabs';

class MindTheGap extends AbstractReport{
    
    function MindTheGap(){
        $report = @$_GET['report'];
        $this->AbstractReport(dirname(__FILE__)."/../ReportXML/$report.xml", -1, false, false);
        wfLoadExtensionMessages("MindTheGap");
        SpecialPage::SpecialPage("MindTheGap", CNI.'+', true);
        $this->showInstructions = false;
    }

    static function createTab(){
		global $wgServer, $wgScriptPath, $wgUser, $wgTitle;
		$person = Person::newFromId($wgUser->getId());
		$page = "MindTheGap?report=MindTheGap";
		
		$selected = "";
		if($wgTitle->getText() == "MindTheGap"){
		    $selected = "selected";
		}
		
		echo "<li class='top-nav-element $selected'>\n";
		echo "	<span class='top-nav-left'>&nbsp;</span>\n";
		echo "	<a id='lnk-my_report' class='top-nav-mid' href='$wgServer$wgScriptPath/index.php/Special:$page' class='new'>Mind The Gap</a>\n";
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
