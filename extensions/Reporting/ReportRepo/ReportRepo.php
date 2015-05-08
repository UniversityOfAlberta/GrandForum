<?php
$dir = dirname(__FILE__) . '/';

$wgSpecialPages['ReportRepo'] = 'ReportRepo';
$wgExtensionMessagesFiles['ReportRepo'] = $dir . 'ReportRepo.i18n.php';
$wgSpecialPageGroups['ReportRepo'] = 'network-tools';

function runReportRepo($par) {
	ReportRepo::run($par);
}

class ReportRepo extends SpecialPage {

	function __construct() {
		wfLoadExtensionMessages('ReportRepo');
		SpecialPage::SpecialPage("ReportRepo", '', false, 'runReportRepo');
	}
	
	function userCanExecute($user){
        if($user->isLoggedIn()){
            $person = Person::newFromWgUser();
            if($person->getName() == "Spencer.Rose" ||
               $person->getName() == "Kellogg.Booth" ||
               $person->isRoleAtLeast(MANAGER)){
                return true;
            }
        }
        return false;
    }
	
	static function run(){
        global $wgServer, $wgScriptPath, $wgOut;
        $wgOut->addHTML("<div id='tabs'>
                          <ul>
                            <li><a href='#tabs-1'>NIs</a></li>
                            <li><a href='#tabs-2'>Projects</a></li>
                          </ul>");
        ReportRepo::showNIs(REPORTING_YEAR);
        ReportRepo::showProjects(REPORTING_YEAR);
        $wgOut->addHTML("</div>");
        $wgOut->addHTML("<script type='text/javascript'>
            $('.datatable').dataTable({'iDisplayLength': -1,
	                                   'aLengthMenu': [[10, 25, 100, 250, -1], [10, 25, 100, 250, 'All']]
	                                  });
	        $('#tabs').tabs();
        </script>");
    }
    
    static function showNIs($year){
        global $wgServer, $wgScriptPath, $wgOut;
        
        $nis = array();
        
        foreach(Person::getAllPeopleDuring(CNI, $year.REPORTING_CYCLE_START_MONTH, ($year+1).REPORTING_CYCLE_END_MONTH) as $person){
            if(!isset($nis[$person->getReversedName()])){
                $nis[$person->getReversedName()] = $person;
            }
        }
        foreach(Person::getAllPeopleDuring(PNI, $year.REPORTING_CYCLE_START_MONTH, ($year+1).REPORTING_CYCLE_END_MONTH) as $person){
            if(!isset($nis[$person->getReversedName()])){
                $nis[$person->getReversedName()] = $person;
            }
        }
        
        $wgOut->addHTML("<div id='tabs-1'>
            <table class='datatable' frame='box' rules='all'>
                <thead>
                    <tr>
                        <th>NI</th>
                        <th width='1%'>Role</th>
                        <th width='1%'>Report PDF</th>
                        <th title='The report has no entries in the text fields'>Started Report?</th>
                        <th title='The Report has been updated since the initial roll-up'>Updated?</th>
                    </tr>
                </thead>
                <tbody>");
        foreach($nis as $ni){
            $report = new DummyReport("NIReport", $ni);
            $started = ($report->hasStarted()) ? "<b style='color:#008800;'>Yes</b>" : 
                                                 "<b style='color:#AA0000;'>No</b>";
            $updated = ($report->hasUpdated()) ? "<b style='color:#008800;'>Yes</b>" : 
                                                 "<b>No</b>";
            $pdf = $report->getLatestPDF();
            $download = "";
            if(isset($pdf[0])){
                $download = "<a href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf={$pdf[0]['token']}' class='button'>Download</a>";
            }
            $role = ($ni->isRoleDuring(PNI, $year.REPORTING_CYCLE_START_MONTH, ($year+1).REPORTING_CYCLE_END_MONTH)) ? PNI : CNI;
            $wgOut->addHTML("<tr>
                                 <td>{$ni->getReversedName()}</td>
                                 <td align='center'>{$role}</td>
                                 <td align='center'>{$download}</td>
                                 <td align='center'>{$started}</td>
                                 <td align='center'>{$updated}</td>
                             </tr>");
        }
        $wgOut->addHTML("</tbody>
            </table>");
        
        $wgOut->addHTML("</div>");
    }
    
    static function showProjects($year){
        global $wgServer, $wgScriptPath, $wgOut;
        $projects = Project::getAllProjects();
        
        $wgOut->addHTML("<div id='tabs-2'>
            <table class='datatable' frame='box' rules='all'>
                <thead>
                    <tr>
                        <th>Project</th>
                        <th width='1%'>Report PDF</th>
                        <th title='The report has no entries in the text fields'>Started Report?</th>
                        <th title='The Report has been updated since the initial roll-up'>Updated?</th>
                    </tr>
                </thead>
                <tbody>");
        foreach($projects as $project){
            if($project->getStatus() != "Active"){
                continue;
            }
            $person = Person::newFromId(4);
            $report = new DummyReport("ProjectReport", $person, $project);
            $started = ($report->hasStarted()) ? "<b style='color:#008800;'>Yes</b>" : 
                                                 "<b style='color:#AA0000;'>No</b>";
            $updated = ($report->hasUpdated()) ? "<b style='color:#008800;'>Yes</b>" : 
                                                 "<b>No</b>";
            $pdf = $report->getLatestPDF();
            $download = "";
            if(isset($pdf[0])){
                $download = "<a href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf={$pdf[0]['token']}' class='button'>Download</a>";
            }
            $wgOut->addHTML("<tr>
                                 <td>{$project->getName()}</td>
                                 <td align='center'>{$download}</td>
                                 <td align='center'>{$started}</td>
                                 <td align='center'>{$updated}</td>
                             </tr>");
        }
        $wgOut->addHTML("</tbody>
            </table>");
        
        $wgOut->addHTML("</div>");
    }
}

?>
