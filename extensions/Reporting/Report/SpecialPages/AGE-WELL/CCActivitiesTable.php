<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['CCActivitiesTable'] = 'CCActivitiesTable'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['CCActivitiesTable'] = $dir . 'CCActivitiesTable.i18n.php';
$wgSpecialPageGroups['CCActivitiesTable'] = 'network-tools';

$wgHooks['SubLevelTabs'][] = 'CCActivitiesTable::createSubTabs';

function runCCActivitiesTable($par) {
    CCActivitiesTable::execute($par);
}

class CCActivitiesTable extends SpecialPage{

    function CCActivitiesTable() {
        SpecialPage::__construct("CCActivitiesTable", null, false, 'runCCActivitiesTable');
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return ($person->isRoleAtLeast(STAFF) || $person->isRole(SD) || $person->isRole(APL));
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        CCActivitiesTable::generateHTML($wgOut);
    }
    
    function generateHTML($wgOut){
        global $wgUser, $wgServer, $wgScriptPath, $wgRoles, $config;
        $year = 2015;
        $projects = Project::getAllProjects();
        $wgOut->addHTML("
            <table id='projectTable' frame='box' rules='all'>
            <thead>
                <tr>
                    <th width='1%'>Project</th>
                    <th width='1%'>Combined&nbsp;PDF&nbsp;Download</th>
                    <th width='1%'>CC1&nbsp;PDF&nbsp;Download</th>
                    <th width='1%'>CC2&nbsp;PDF&nbsp;Download</th>
                    <th width='1%'>CC3&nbsp;PDF&nbsp;Download</th>
                    <th width='1%'>CC4&nbsp;PDF&nbsp;Download</th>
                </tr>
            </thead>
            <tbody>");
        foreach($projects as $project){
            if($project->getType() == 'Administrative'){
                continue;
            }
            $wgOut->addHTML("<tr>");
            $wgOut->addHTML("<td style='white-space:nowrap;'>{$project->getName()}</td>");
        
            $combined = new DummyReport(RP_CC_PLANNING, new Person(array()), $project, REPORTING_YEAR, true);
            $cc1 = new DummyReport("CC1PlanningPDF", new Person(array()), $project, REPORTING_YEAR, true);
            $cc2 = new DummyReport("CC2PlanningPDF", new Person(array()), $project, REPORTING_YEAR, true);
            $cc3 = new DummyReport("CC3PlanningPDF", new Person(array()), $project, REPORTING_YEAR, true);
            $cc4 = new DummyReport("CC4PlanningPDF", new Person(array()), $project, REPORTING_YEAR, true);
            $check = $combined->getLatestPDF();
            $check1 = $cc1->getLatestPDF();
            $check2 = $cc2->getLatestPDF();
            $check3 = $cc3->getLatestPDF();
            $check4 = $cc4->getLatestPDF();
            
            $pdfUrl = "";
            $pdf1Url = "";
            $pdf2Url = "";
            $pdf3Url = "";
            $pdf4Url = "";

            if(isset($check[0])){
                $pdf = PDF::newFromToken($check[0]['token']);
                $pdfUrl = "<a class='button' href='{$pdf->getUrl()}'>Download</a>";
            }
            if(isset($check1[0]) && self::checkSection($project, $year, CC_PLANNING_1)){
                $pdf1 = PDF::newFromToken($check1[0]['token']);
                $pdf1Url = "<a class='button' href='{$pdf1->getUrl()}'>Download</a>";
            }
            if(isset($check2[0]) && self::checkSection($project, $year, CC_PLANNING_2)){
                $pdf2 = PDF::newFromToken($check2[0]['token']);
                $pdf2Url = "<a class='button' href='{$pdf2->getUrl()}'>Download</a>";
            }
            if(isset($check3[0]) && self::checkSection($project, $year, CC_PLANNING_3)){
                $pdf3 = PDF::newFromToken($check3[0]['token']);
                $pdf3Url = "<a class='button' href='{$pdf3->getUrl()}'>Download</a>";
            }
            if(isset($check4[0]) && self::checkSection($project, $year, CC_PLANNING_4)){
                $pdf4 = PDF::newFromToken($check4[0]['token']);
                $pdf4Url = "<a class='button' href='{$pdf4->getUrl()}'>Download</a>";
            }
            
            $wgOut->addHTML("<td align='center'>$pdfUrl</td>");
            $wgOut->addHTML("<td align='center'>$pdf1Url</td>");
            $wgOut->addHTML("<td align='center'>$pdf2Url</td>");
            $wgOut->addHTML("<td align='center'>$pdf3Url</td>");
            $wgOut->addHTML("<td align='center'>$pdf4Url</td>");
            $wgOut->addHTML("</tr>");
        }
        $wgOut->addHTML("</tbody>
        </table>");
        $wgOut->addHTML("<script type='text/javascript'>
            $('#projectTable').dataTable({'iDisplayLength': 100});
        </script>");
    }
    
    static function checkSection($project, $year, $section){
        $data = DBFunctions::select(array('grand_report_blobs'),
                                    array('data'),
                                    array('proj_id' => EQ($project->getId()),
                                          'year' => EQ($year),
                                          'rp_type' => EQ(RP_CC_PLANNING),
                                          'rp_section' => EQ($section)));
        foreach($data as $row){
            if($row['data'] != ""){
                // At least one of the fields was filled out
                return true;
            }
        }
        return false;
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        if((new self)->userCanExecute($wgUser)){
            $selected = @($wgTitle->getText() == "CCActivitiesTable") ? "selected" : false;
            $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("CC Activities Table", "$wgServer$wgScriptPath/index.php/Special:CCActivitiesTable", $selected);
        }
        return true;
    }

}

?>
