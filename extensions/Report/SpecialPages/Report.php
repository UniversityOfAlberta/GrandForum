<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['Report'] = 'Report'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['Report'] = $dir . 'Report.i18n.php';
$wgSpecialPageGroups['Report'] = 'reporting-tools';

$wgHooks['SkinTemplateContentActions'][] = 'Report::showTabs';

class Report extends AbstractReport{
    
    function Report(){
        $report = @$_GET['report'];
        $topProjectOnly = false;
        if(isset($_GET['project']) && ($report == "NIReport" || $report == "HQPReport")){
            $topProjectOnly = true;
        }
        $this->AbstractReport(dirname(__FILE__)."/../ReportXML/$report.xml", -1, false, $topProjectOnly);
    }

    static function createTab(){
		global $wgServer, $wgScriptPath, $wgUser, $wgTitle;
		$person = Person::newFromId($wgUser->getId());
		$page = "Report";
		if($person->isRoleDuring(HQP, REPORTING_CYCLE_START, REPORTING_CYCLE_END)){
		    $page = "Report?report=HQPReport";
		}
		else if($person->isRoleDuring(CNI, REPORTING_CYCLE_START, REPORTING_CYCLE_END) || 
		        $person->isRoleDuring(PNI, REPORTING_CYCLE_START, REPORTING_CYCLE_END) || 
		        $person->isRoleAtLeast(MANAGER)){
		    $page = "Report?report=NIReport";
		}
		else if(count($person->leadership()) > 0){
		    $projects = $person->leadership();
		    $project = $projects[0];
		    if($project->isDeleted() && substr($project->getEffectiveDate(), 0, 4) == REPORTING_YEAR){
		        $page = "Report?report=ProjectFinalReport&project={$project->getName()}";
		    }
		    else if(!$project->isDeleted()){
		        $page = "Report?report=ProjectReport&project={$project->getName()}";
		    }
		}
		else if($person->isEvaluator()){
            $page = "Report?report=EvalReport";
		}
		
		$selected = "";
		if($wgTitle->getText() == "Report"){
		    $selected = "selected";
		}
		
		echo "<li class='top-nav-element $selected'>\n";
		echo "	<span class='top-nav-left'>&nbsp;</span>\n";
		echo "	<a id='lnk-my_report' class='top-nav-mid' href='$wgServer$wgScriptPath/index.php/Special:$page' class='new'>My Reports</a>\n";
		echo "	<span class='top-nav-right'>&nbsp;</span>\n";
		echo "</li>";
	}
    
    static function showTabs(&$content_actions){
        global $wgTitle, $wgUser, $wgServer, $wgScriptPath;
        if($wgTitle->getText() == "Report"){
            $content_actions = array();
            $person = Person::newFromId($wgUser->getId());
            
            // Individual Report
            if($person->isRoleDuring(HQP, REPORTING_CYCLE_START, REPORTING_CYCLE_END) || $person->isRoleAtLeast(MANAGER)){
                $class = @($wgTitle->getText() == "Report" && ($_GET['report'] == "HQPReport")) ? "selected" : false;
                $text = HQP;
                $content_actions[] = array (
                         'class' => $class,
                         'text'  => $text,
                         'href'  => "$wgServer$wgScriptPath/index.php/Special:Report?report=HQPReport",
                        );
            }
            if($person->isRoleDuring(CNI, REPORTING_CYCLE_START, REPORTING_CYCLE_END) || $person->isRoleDuring(PNI, REPORTING_CYCLE_START, REPORTING_CYCLE_END) || $person->isRoleAtLeast(MANAGER)){
                $class = @($wgTitle->getText() == "Report" && ($_GET['report'] == "NIReport")) ? "selected" : false;
                $text = "Individual";
                if($person->isRoleDuring(PNI, REPORTING_CYCLE_START, REPORTING_CYCLE_END))
                    $text = PNI;
                else if($person->isRoleDuring(CNI, REPORTING_CYCLE_START, REPORTING_CYCLE_END))
                    $text = CNI;
                $content_actions[] = array (
                         'class' => $class,
                         'text'  => $text,
                         'href'  => "$wgServer$wgScriptPath/index.php/Special:Report?report=NIReport",
                        );
            }
            
            // Project Leader Report
            $leadership = $person->leadership();
            if(count($leadership) > 0){
                foreach($leadership as $project){
                    if($project->isDeleted() && substr($project->getEffectiveDate(), 0, 4) == REPORTING_YEAR){
		                $type = "ProjectFinalReport";
		            }
		            else if(!$project->isDeleted()){
		                $type = "ProjectReport";
		            }
		            else{
		                continue;
		            }
                    @$class = ($wgTitle->getText() == "Report" && $_GET['report'] == "$type" && $_GET['project'] == $project->getName()) ? "selected" : false;
                    $content_actions[] = array (
                             'class' => $class,
                             'text'  => "{$project->getName()}",
                             'href'  => "$wgServer$wgScriptPath/index.php/Special:Report?report=$type&project={$project->getName()}",
                            );
                }
            }
            
            // Evaluator Report
            
            if($person->isEvaluator()){
                    @$class = ($wgTitle->getText() == "Report" && $_GET['report'] == "EvalReport") ? "selected" : false;
                    
                    $content_actions[] = array (
                             'class' => $class,
                             'text'  => "Evaluator",
                             'href'  => "$wgServer$wgScriptPath/index.php/Special:Report?report=EvalReport",
                            );
            }
            
        }
        return true;
    }
}

?>
