<?php

class ProjectSummaryTab extends AbstractTab {

    var $project;
    var $visibility;

    function ProjectSummaryTab($project, $visibility){
        parent::AbstractTab("Summary");
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
            if(isset($_POST['start']) && isset($_POST['end'])){
                $start = $_POST['start'];
                $end = $_POST['end'];
            }
            if(isset($_POST['download'])){
                $_GET['generatePDF'] = true;
                $dashboard = new DashboardTable(PROJECT_REPORT_PRODUCTIVITY_STRUCTURE, $project, $start, $end);
                $html = "<h1>{$project->getName()} Dashboard Summary ($start - $end)</h1>";
                $html .= $dashboard->renderForPDF(true, false);
                $html .= "<div class='pagebreak'></div>";
                $html .= $dashboard->renderForPDF(false, true);
                PDFGenerator::generate("{$project->getName()} Dashboard Summary", $html, "", null, null, false, null, true);
            }
            $this->html .= "<form method='post' action='?tab=summary#'>
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
            $dashboard = new DashboardTable(PROJECT_REPORT_PRODUCTIVITY_STRUCTURE, $project, $start, $end);
            $this->html .= $dashboard->render(false, false);
            $this->html .= "</form>";
        }
    }

}    
    
?>
