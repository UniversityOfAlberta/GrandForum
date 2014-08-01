<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['ProjectLeadership'] = 'ProjectLeadership'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['ProjectLeadership'] = $dir . 'ProjectLeadership.i18n.php';
$wgSpecialPageGroups['ProjectLeadership'] = 'network-tools';

function runProjectLeadership($par){
    ProjectLeadership::run($par);
}

class ProjectLeadership extends SpecialPage {
    
    function ProjectLeadership(){
        wfLoadExtensionMessages('ProjectLeadership');
		SpecialPage::SpecialPage("ProjectLeadership", STAFF.'+', true, 'runProjectLeadership');
    }    
    
    function run(){
        global $wgOut;
        $projects = Project::getAllProjectsEver();
        $wgOut->addHTML("<table cellpadding='3' frame='box' rules='all'>
            <thead>
                <th>Project</th>
                <th colspan='4'>Leaders</th>
                <th colspan='4'>Co-Leaders</th>
            </thead>
            <tbody>");
        foreach($projects as $project){
            if($project->isDeleted()){
                $wgOut->addHTML("<tr>");
                    $wgOut->addHTML("<td>{$project->getName()}</td>");
                    // Leaders
                    $names = array();
                    $starts = array();
                    $ends = array();
                    $universities = array();
                    foreach($project->getLeadersHistory() as $leader){
                        $names[] = $leader->getReversedName();
                        $starts[] = $leader->getLeaderStartDate($project);
                        $ends[] = $leader->getLeaderEndDate($project);
                        $universities[] = $leader->getUni();
                    }
                    $wgOut->addHTML("<td>".implode("<br />", $names)."</td><td>".implode("<br />", $universities)."</td><td>".implode("<br />", $starts)."</td><td>".implode("<br />", $ends)."</td>");
                    // Co-Leaders
                    $names = array();
                    $starts = array();
                    $ends = array();
                    $universities = array();
                    foreach($project->getCoLeadersHistory() as $leader){
                        $names[] = $leader->getReversedName();
                        $starts[] = $leader->getCoLeaderStartDate($project);
                        $ends[] = $leader->getCoLeaderEndDate($project);
                        $universities[] = $leader->getUni();
                    }
                    $wgOut->addHTML("<td>".implode("<br />", $names)."</td><td>".implode("<br />", $universities)."</td><td>".implode("<br />", $starts)."</td><td>".implode("<br />", $ends)."</td>");
                $wgOut->addHTML("</tr>");
            }
        }
        $wgOut->addHTML("</tbody></table>");
        return true;
    }
}
?>
