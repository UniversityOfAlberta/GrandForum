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

    function __construct() {
        SpecialPage::__construct("ReportStatusTable", null, false, 'runReportStatusTable');
    }
    
    function userCanExecute($user){
        $person = Person::newFromUser($user);
        return ($person->isRoleAtLeast(STAFF) || $person->isRole(SD));
    }

    function execute($par){
        global $wgOut, $wgUser, $wgServer, $wgScriptPath, $wgTitle, $wgMessage;
        $this->getOutput()->setPageTitle("Report Status Table");
        ReportStatusTable::generateHTML($wgOut);
    }
    
    function generateHTML($wgOut){
        global $wgUser, $wgServer, $wgScriptPath, $wgRoles, $config;
        $hqps = Person::getAllPeopleDuring(HQP, "0000-00-00", "2100-00-00");
        $nis = Person::getAllPeopleDuring(NI, "0000-00-00", "2100-00-00");
        $ifpFinal = array();
        $ifpProgress = array();
        $ifp2016HQP = array();
        $ifp2017HQP = array();
        $ifp2018HQP = array();
        $ifp2019HQP = array();
        $ifp2020HQP = array();
        $ifp2016Final = array(
            Person::newFromId(235),
            Person::newFromId(308),
            Person::newFromId(325),
            Person::newFromId(347));
        $ssa = array();
        $ssa2016 = array();
        $ssa2017 = array();
        $ssa2018 = array();
        $ssa2019 = array();
        $ssa2020 = array();
        $ssa2021 = array();
        foreach($hqps as $hqp){
            if($hqp->isSubRole('IFP')){
                $ifpDeleted = false;
                $ifp2016 = false;
                $ifp2017 = false;
                $ifp2018 = false;
                $ifp2019 = false;
                $ifp2020 = false;
                foreach($hqp->leadershipDuring("0000-00-00", "2100-00-00") as $project){
                    $ifpDeleted = ($ifpDeleted || ($project->isDeleted() && (strstr($project->getName(), "IFP") !== false || strstr($project->getName(), "IFA") !== false)));
                    $ifp2016 = ($ifp2016 || strstr($project->getName(), "IFP2016") !== false || strstr($project->getName(), "IFA2016") !== false);
                    $ifp2017 = ($ifp2017 || strstr($project->getName(), "IFP2017") !== false || strstr($project->getName(), "IFA2017") !== false);
                    $ifp2018 = ($ifp2018 || strstr($project->getName(), "IFP2018") !== false || strstr($project->getName(), "IFA2018") !== false);
                    $ifp2019 = ($ifp2019 || strstr($project->getName(), "IFP2019") !== false || strstr($project->getName(), "IFA2019") !== false);
                    $ifp2020 = ($ifp2020 || strstr($project->getName(), "IFP2020") !== false || strstr($project->getName(), "IFA2020") !== false);
                }
                if($ifp2020){
                    $ifp2020HQP[] = $hqp;
                }
                if($ifp2019){
                    $ifp2019HQP[] = $hqp;
                }
                if($ifp2018){
                    $ifp2018HQP[] = $hqp;
                }
                if($ifp2017){
                    $ifp2017HQP[] = $hqp;
                }
                if($ifp2016){
                    $ifp2016HQP[] = $hqp;
                }
                if(!$ifpDeleted){
                    $ifpProgress[] = $hqp;
                }
                if(!in_array($hqp, $ifp2016Final)){
                    $ifpFinal[] = $hqp;
                }
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
        foreach(Project::getAllProjectsEver() as $project){
            if(strstr($project->getName(), "SSA2016") !== false){
                $ssa2016[$project->getName()] = $project;
            }
            else if(strstr($project->getName(), "SSA2017") !== false){
                $ssa2017[$project->getName()] = $project;
            }
            else if(strstr($project->getName(), "SSA2018") !== false){
                $ssa2018[$project->getName()] = $project;
            }
            else if(strstr($project->getName(), "SSA2019") !== false){
                $ssa2019[$project->getName()] = $project;
            }
            else if(strstr($project->getName(), "SSA2020") !== false){
                $ssa2020[$project->getName()] = $project;
            }
            else if(strstr($project->getName(), "SSA2021") !== false){
                $ssa2021[$project->getName()] = $project;
            }
        }
        $wgOut->addHTML("<div id='tabs'>
                            <ul>
                                <li><a href='#final'>Final Project</a></li>
                                <li><a href='#progress'>Project Progress</a></li>
                                <li><a href='#ifp_final_2015'>IFP2015 Final</a></li>
                                <li><a href='#ifp_progress_2015'>IFP2015 Progress</a></li>
                                <li><a href='#ifp_final_2016'>IFP2016 Final (Spec)</a></li>
                                <li><a href='#ifp_progress_2016'>IFP2016 Progress </a></li>
                                <li><a href='#ifp2016_final_2016'>IFP2016 Final</a></li>
                                <li><a href='#ifp_progress_2017'>IFP2017 Progress</a></li>
                                <li><a href='#ifp2017_final_2017'>IFP2017 Final</a></li>
                                <li><a href='#ifp_progress_2018'>IFP2018 Progress</a></li>
                                <li><a href='#ifp2018_final_2018'>IFP2018 Final</a></li>
                                <li><a href='#ifp_progress_2019'>IFP2019 Progress</a></li>
                                <li><a href='#ifp2019_final_2019'>IFP2019 Final</a></li>
                                <li><a href='#ifp_progress_2020'>IFP2020 Progress</a></li>
                                <li><a href='#ifp2020_final_2020'>IFP2020 Final</a></li>
                                <li><a href='#ssa'>SSA2015</a></li>
                                <li><a href='#ssa2016'>SSA2016</a></li>
                                <li><a href='#ssa2017'>SSA2017</a></li>
                                <li><a href='#ssa2018'>SSA2018</a></li>
                                <li><a href='#ssa2019'>SSA2019</a></li>
                                <li><a href='#ssa2020'>SSA2020</a></li>
                                <li><a href='#ssa2021'>SSA2021</a></li>
                            </ul>");
        $this->addProjectTable(RP_FINAL_PROJECT,    'final',              2015);
        $this->addProjectTable(RP_PROGRESS,         'progress',           2015);
        $this->addTable(RP_IFP_FINAL_PROJECT,       'ifp_final_2015',     $ifpFinal, 2015);
        $this->addTable(RP_IFP_PROGRESS,            'ifp_progress_2015',  $ifpProgress, 2015);
        $this->addTable(RP_IFP_FINAL_PROJECT,       'ifp_final_2016',     $ifp2016Final, 2015);
        $this->addTable(RP_IFP_PROGRESS,            'ifp_progress_2016',  $ifp2016HQP, 2016);
        $this->addTable(RP_IFP_FINAL_PROJECT,       'ifp2016_final_2016', $ifp2016HQP, 2016);
        $this->addTable(RP_IFP_PROGRESS,            'ifp_progress_2017',  $ifp2017HQP, 2017);
        $this->addTable(RP_IFP_FINAL_PROJECT,       'ifp2017_final_2017', $ifp2017HQP, 2017);
        $this->addTable(RP_IFP_PROGRESS,            'ifp_progress_2018',  $ifp2018HQP, 2018);
        $this->addTable(RP_IFP_FINAL_PROJECT,       'ifp2018_final_2018', $ifp2018HQP, 2018);
        $this->addTable(RP_IFP_PROGRESS,            'ifp_progress_2019',  $ifp2019HQP, 2019);
        $this->addTable(RP_IFP_FINAL_PROJECT,       'ifp2019_final_2019', $ifp2019HQP, 2019);
        $this->addTable(RP_IFP_PROGRESS,            'ifp_progress_2020',  $ifp2020HQP, 2020);
        $this->addTable(RP_IFP_FINAL_PROJECT,       'ifp2020_final_2020', $ifp2020HQP, 2020);
        $this->addTable('HQPReport',                'ssa',                $ssa, 2015);
        $this->addProjectTable('SSAReport',         'ssa2016',            2016, $ssa2016);
        $this->addProjectTable('SSAReport',         'ssa2017',            2017, $ssa2017);
        $this->addProjectTable('SSAReport',         'ssa2018',            2018, $ssa2018);
        $this->addProjectTable('SSAReport',         'ssa2019',            2019, $ssa2019);
        $this->addProjectTable('SSAReport',         'ssa2020',            2020, $ssa2020);
        $this->addProjectTable('SSAReport',         'ssa2021',            2021, $ssa2021);
        $wgOut->addHTML("</div>");
        $wgOut->addHTML("<script type='text/javascript'>
            $('#tabs').tabs();
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
            $projects = Project::getAllProjectsEver();
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
        if((new self)->userCanExecute($wgUser)){
            $selected = @($wgTitle->getText() == "ReportStatusTable") ? "selected" : false;
            $tabs["Manager"]['subtabs'][] = TabUtils::createSubTab("Report Status", "$wgServer$wgScriptPath/index.php/Special:ReportStatusTable", $selected);
        }
        return true;
    }

}

?>
