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
        $hqps = Person::getAllPeople(HQP);
        $nis = Person::getAllPeople(NI);
        $ifp = array();
        $ssa = array();
        foreach($hqps as $hqp){
            if($hqp->isSubRole('IFP')){
                $ifp[] = $hqp;
            }
        }
        foreach($nis as $ni){
            foreach($ni->getHQP() as $hqp){
                if($hqp->isSubRole('SSA')){
                    $ssa[$ni->getId()] = $ni;
                    break;
                }
            }
        }
        $wgOut->addHTML("<div id='tabs'>
                            <ul>
                                <li><a href='#final'>Final Project Report</a></li>
                                <li><a href='#progress'>Project Progress Report</a></li>
                                <li><a href='#ifp_final'>IFP Final Report</a></li>
                                <li><a href='#ifp_progress'>IFP Progress Report</a></li>
                                <li><a href='#ssa'>SSA Report</a></li>
                            </ul>");
        $this->addProjectTable(RP_FINAL_PROJECT,    'final');
        $this->addProjectTable(RP_PROGRESS,         'progress');
        $this->addTable(RP_IFP_FINAL_PROJECT,       'ifp_final',    $ifp);
        $this->addTable(RP_IFP_PROGRESS,            'ifp_progress', $ifp);
        $this->addTable(RP_SSA_FINAL_PROGRESS,      'ssa',          $ssa);
        $wgOut->addHTML("</div>");
        $wgOut->addHTML("<script type='text/javascript'>
            $('#tabs').tabs();
        </script>");
    }
    
    function addTable($rp, $type, $people){
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
            $report = new DummyReport($rp, $person, null);
            $check = $report->getLatestPDF();
            $generated = "";
            $download = "";
            if(isset($check[0])){
                $pdf = PDF::newFromToken($check[0]['token']);
                $generated = time2date($check[0]['timestamp'], 'F j, Y h:i:s');
                $download = "<a class='button' href='{$pdf->getUrl()}'>Download</a>";
            }
            $started = ($report->hasStarted()) ? "Yes" : "No";
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
            $('#{$type}Table').dataTable();
        </script>");
        $wgOut->addHTML("</div>");
    }
    
    function addProjectTable($rp, $type){
        global $wgOut;
        $projects = Project::getAllProjects();
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
                if($leader->isRole(NI, $project)){
                    $report = new DummyReport($rp, $leader, $project);
                    $check = $report->getLatestPDF();
                    $generated = "";
                    $download = "";
                    if(isset($check[0])){
                        $pdf = PDF::newFromToken($check[0]['token']);
                        $generated = time2date($check[0]['timestamp'], 'F j, Y h:i:s');
                        $download = "<a class='button' href='{$pdf->getUrl()}'>Download</a>";
                    }
                    $started = ($report->hasStarted()) ? "Yes" : "No";
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
            $('#{$type}Table').dataTable();
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
