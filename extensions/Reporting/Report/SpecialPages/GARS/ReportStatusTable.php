<?php

$dir = dirname(__FILE__) . '/';
$wgSpecialPages['ReportStatusTable'] = 'ReportStatusTable'; # Let MediaWiki know about the special page.
$wgExtensionMessagesFiles['ReportStatusTable'] = $dir . 'ReportStatusTable.i18n.php';
$wgSpecialPageGroups['ReportStatusTable'] = 'network-tools';

$wgHooks['SubLevelTabs'][] = 'ReportStatusTable::createSubTabs';

function runReportStatusTable($par) {
    ReportStatusTable::execute($par);
}

class ReportStatusTable extends SpecialPage{

    function ReportStatusTable() {
        SpecialPage::__construct("ReportStatusTable", null, false, 'runReportStatusTable');
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return ($person->isRoleAtLeast(STAFF) || $person->isRole(SD));
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        ReportStatusTable::generateHTML($wgOut);
    }
    
    function generateHTML($wgOut){
        global $wgUser, $wgServer, $wgScriptPath, $wgRoles, $config;
        $ots = Person::getAllPeople(CI);
        
        /*$wgOut->addHTML("<div id='tabs'>
                            <ul>
                                <li><a href='#ot'>OT Questionnaire</a></li>
                            </ul>");*/
        $this->addTable('RP_OT',       'ot_questionnaire',     $ots);
        //$wgOut->addHTML("</div>");
        $wgOut->addHTML("<script type='text/javascript'>
            //$('#tabs').tabs();
            $('#bodyContent > h1').hide();
        </script>");
    }
    
    function addTable($rp, $type, $people, $year = REPORTING_YEAR){
        global $wgOut;
        $wgOut->addHTML("
            <div id='{$type}'>
            <table id='{$type}Table' frame='box' rules='all'>
            <thead>
                <tr>
                    <th width='1%'>First&nbsp;Name</th>
                    <th width='1%'>Last&nbsp;Name</th>
                    <th width='1%'>Email</th>
                    <th width='1%'>Started?</th>
                    <th width='1%'>Generation&nbsp;Date (MST)</th>
                    <th width='1%'>PDF&nbsp;Download</th>
                </tr>
            </thead>
            <tbody>");
        foreach($people as $person){
            $report = new DummyReport($rp, $person, null, $year, true);
            $report->year = $year;
            $generated = "";
            $download = "";
            
            $started = ($report->hasStarted()) ? "Yes" : "No";
            if($started == "Yes"){
                $check = $report->getLatestPDF();
                if(isset($check[0])){
                    $pdf = PDF::newFromToken($check[0]['token']);
                    $generated = time2date($check[0]['timestamp'], 'F j, Y h:i:s');
                    $download = "<a class='button' href='{$pdf->getUrl()}'>Download</a>";
                }
            }
            $wgOut->addHTML("<tr>");
            $wgOut->addHTML("<td>{$person->getFirstName()}</td><td>{$person->getLastName()}</td><td><a href='mailto:{$person->getEmail()}'>{$person->getEmail()}</a></td>");
            $wgOut->addHTML("<td align='center'>{$started}</td>");
            $wgOut->addHTML("<td style='white-space:nowrap;'>{$generated}</td>");
            $wgOut->addHTML("<td align='center'>{$download}</td>");
            $wgOut->addHTML("</tr>");
        }
        $wgOut->addHTML("</tbody>
        </table>");
        $wgOut->addHTML("<script type='text/javascript'>
            $('#{$type}Table').dataTable({'iDisplayLength': 25});
        </script>");
        $wgOut->addHTML("</div>");
    }
    
    function addProjectTable($rp, $type, $year = REPORTING_YEAR, $projects=null){
        global $wgOut;
        $showAll = true;
        if($projects === null){
            $showAll = false;
            $projects = Project::getAllProjects();
        }
        $wgOut->addHTML("
            <div id='{$type}'>
            <table id='{$type}Table' frame='box' rules='all'>
            <thead>
                <tr>
                    <th width='1%'>First&nbsp;Name</th>
                    <th width='1%'>Last&nbsp;Name</th>
                    <th width='1%'>Email</th>
                    <th>Project Title</th>
                    <th width='1%'>Started?</th>
                    <th width='1%'>Generation&nbsp;Date (MST)</th>
                    <th width='1%'>PDF&nbsp;Download</th>
                </tr>
            </thead>
            <tbody>");
        foreach($projects as $project){
            $leaders = array_values($project->getLeaders());
            if(isset($leaders[0])){
                $leader = $leaders[0];
                if((!$leader->isRole(HQP) || $showAll) && $leader->isActive()){
                    $report = new DummyReport($rp, $leader, $project, $year, true);
                    $report->year = $year;
                    $generated = "";
                    $download = "";
                    
                    $started = ($report->hasStarted()) ? "Yes" : "No";
                    if($started == "Yes"){
                        $check = $report->getLatestPDF();
                        if(isset($check[0])){
                            $pdf = PDF::newFromToken($check[0]['token']);
                            $generated = time2date($check[0]['timestamp'], 'F j, Y h:i:s');
                            $download = "<a class='button' href='{$pdf->getUrl()}'>Download</a>";
                        }
                    }
                    $wgOut->addHTML("<tr>");
                    $wgOut->addHTML("<td>{$leader->getFirstName()}</td><td>{$leader->getLastName()}</td><td><a href='mailto:{$leader->getEmail()}'>{$leader->getEmail()}</a></td>");
                    $wgOut->addHTML("<td>{$project->getName()}</td>");
                    $wgOut->addHTML("<td align='center'>{$started}</td>");
                    $wgOut->addHTML("<td style='white-space:nowrap;'>{$generated}</td>");
                    $wgOut->addHTML("<td align='center'>{$download}</td>");
                    $wgOut->addHTML("</tr>");
                }
            }
        }
        $wgOut->addHTML("</tbody>
        </table>");
        $wgOut->addHTML("<script type='text/javascript'>
            $('#{$type}Table').dataTable({'iDisplayLength': 25});
        </script>
        </div>");
    }
    
    static function createSubTabs(&$tabs){
        global $wgServer, $wgScriptPath, $wgUser, $wgTitle, $special_evals;
        if(self::userCanExecute($wgUser)){
            $selected = @($wgTitle->getText() == "ReportStatusTable") ? "selected" : false;
            $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("Report Status", "$wgServer$wgScriptPath/index.php/Special:ReportStatusTable", $selected);
        }
        return true;
    }

}

?>
