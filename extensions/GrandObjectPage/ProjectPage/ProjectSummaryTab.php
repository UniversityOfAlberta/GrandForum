<?php

class ProjectSummaryTab extends AbstractTab {

    var $project;
    var $visibility;

    function __construct($project, $visibility){
        parent::__construct("Summary");
        $this->project = $project;
        $this->visibility = $visibility;
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath;
        if(!$wgUser->isLoggedIn()){
            return;
        }
        $this->showDashboard($this->project, $this->visibility);
        return $this->html;
    }
    
    function showDashboard($project, $visibility){
        global $wgOut, $config, $wgServer, $wgScriptPath;
        $me = Person::newFromWgUser();
        if($me->isLoggedIn()){
            $phaseDates = $config->getValue('projectPhaseDates');
            $start = substr($phaseDates[1], 0, 10);
            $end = date('Y-m-d');
            if(isset($_GET['start']) && isset($_GET['end'])){
                $start = $_GET['start'];
                $end = $_GET['end'];
            }
            if(isset($_GET['submit']) || isset($_GET['download'])){
                $dashboard = new DashboardTable(PROJECT_REPORT_PRODUCTIVITY_STRUCTURE, $project, $start, $end);
                $contributions = new DashboardTable(PROJECT_CONTRIBUTION_STRUCTURE, $project, $start, $end);
                $dashboard = $dashboard->copy()->join($contributions->copy()->select(HEAD, array("Contributions")));
            }
            if(isset($_GET['download'])){
                $_GET['generatePDF'] = true;
                $html = "<h1>{$project->getName()} Summary ($start - $end)</h1>";
                $html .= $dashboard->renderForPDF(true, false);
                $html .= "<div class='pagebreak'></div>";
                $html .= $dashboard->renderForPDF(false, true);
                header('Content-Disposition: inline; filename="Dashboard.pdf"');
                PDFGenerator::generate("{$project->getName()} Summary", $html, "", null, null, false, null, true);
            }
            $this->html .= "<form method='get' action='?tab=summary'>
                <input type='hidden' name='tab' value='summary' />
                <table>
                    <tr>
                        <th>Start Date</th><th>End Date</th><th></th>
                    </tr>
                    <tr>
                        <td><input type='text' name='start' value='$start' size='10' /></td>
                        <td><input type='text' name='end' value='$end' size='10' /></td>
                        <td><input type='submit' name='submit' value='Submit' /> <input type='submit' name='download' value='Download as PDF' /></td>
                    </tr>
                </table>
                <br />
            <script type='text/javascript'>
                $('input[name=start]').datepicker({
                    'dateFormat': 'yy-mm-dd',
                    'defaultDate': '$start',
                    'changeMonth': true,
                    'changeYear': true,
                    'showOn': 'both',
                    'buttonImage': '$wgServer$wgScriptPath/skins/calendar.gif',
                    'buttonImageOnly': true
                });
                $('input[name=end]').datepicker({
                    'dateFormat': 'yy-mm-dd',
                    'defaultDate': '$end',
                    'changeMonth': true,
                    'changeYear': true,
                    'showOn': 'both',
                    'buttonImage': '$wgServer$wgScriptPath/skins/calendar.gif',
                    'buttonImageOnly': true
                });
            </script>";
            if(isset($_GET['submit'])){
                $this->html .= $dashboard->render(false, false);
            }
            $this->html .= "</form>";
        }
    }

}    
    
?>
