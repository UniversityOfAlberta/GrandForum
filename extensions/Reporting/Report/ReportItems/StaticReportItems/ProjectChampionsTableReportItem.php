<?php

class ProjectChampionsTableReportItem extends StaticReportItem {

    private function getTable($pdf=false){
        $project = Project::newFromId($this->projectId);
        $subs = $project->getSubProjects();
        $table = "";
        if($pdf){
            $table .= "<table cellspacing='1' class='dashboard' width='100%' style='width:100%;background-color:#000000;border-color:#000000;margin-bottom:15px;border-spacing:".max(1, (0.5*DPI_CONSTANT))."px;'>";
        }
        else{
            $table .= "<table class='dashboard' width='100%' style='border-spacing: 1px;width:100%;max-width:900px;background-color:#808080;' frame='box' rules='all'>";
        }
        $table .= "<tr style='background: #FFFFFF;'><td></td><td></td><td></td><td></td><td></td>";
        foreach($subs as $sub){
            $table .= "<td class='small' style='display:table-cell;' align='center'><b>{$sub->getName()}</b></td>";
        }
        $table .= "</tr>
                   <tr style='background: #FFFFFF;'>
                        <td class='small' style='background: #DDDDDD;' align='right'><b>First&nbsp;</b></td>
                        <td class='small' style='background: #DDDDDD;' align='left'><b>&nbsp;Last</b></td>
                        <td class='small' style='background: #DDDDDD;'><b>Organization</b></td>
                        <td class='small' style='background: #DDDDDD;' align='center'><b>#Subs</b></td>
                        <td class='small' style='background: #DDDDDD;' align='center'><b>Submitted</b></td>";
        $champions = $project->getChampions();
        foreach($subs as $sub){
            $count = 0;
            foreach($sub->getChampions() as $champ){
                $found = false;
                foreach($champions as $c){
                    if($c['user']->getId() == $champ['user']->getId()){
                        $found = true;
                    }
                }
                if(!$found){
                    $champions[] = $champ;
                }
                $count++;
            }
            $table .= "<td class='small' style='background: #DDDDDD;' align='center'><b>{$count}</b></td>";
        }
        $table .= "</tr>";
        foreach($champions as $c){
            $champion = $c['user'];
            $org = $champion->getPartnerName();
            if($org == ""){
                $uni = University::newFromName($champion->getUni());
            }
            else{
                $uni = University::newFromName($org);
            }
            if($uni->getName() != ""){
                $org = $uni->getShortName();
            }
            
            $table .= "<tr style='background: #FFFFFF;'>";
            $table .= "<td class='small' align='right'>{$champion->getFirstName()}&nbsp;</td>";
            $table .= "<td class='small' align='left'>&nbsp;{$champion->getLastName()}</td>";
            $table .= "<td class='small'>{$org}</td>";
            $count = 0;
            $subHTML = "";
            foreach($subs as $sub){
                if($champion->isMemberOf($sub)){
                    $count++;
                    $time = $champion->getTimeOnProject($sub, "%m");
                    if($time == 0){
                        $time = 1;
                    }
                    $subHTML .= "<td class='small' align='center'>{$time}</td>";
                }
                else{
                    $subHTML .= "<td class='small'></td>";
                }
            }
            $report = new DummyReport(RP_CHAMP, $champion, $project, $this->getReport()->year);
            $submitted = ($report->isSubmitted()) ? "<span style='font-weight:bold;color:#008800;'>Yes</span>" : "<span>N/A</span>";
            $table .= "<td class='small' align='center' style='background: #DDDDDD;font-weight:bold;'>{$count}</td><td class='small' align='center'>$submitted</td>$subHTML</tr>";
        }
        $table .= "</table>";
        $table .= "<small>
            <ul>
                <li>The numbers in the middle of the table (6th column and onward) represent the number of months that the Champion has been with the project.</li>
                <li>The numbers on the top edge represent (2nd row) the number of Champions are on each Sub-Project.</li>
                <li>The numbers in the 4th column represent the number of Sub-Projects the Champion is on.</li>
                <li>The numbers in the 5th column represent whether the Champion has submitted their report or not.</li>
            </ul>
            </small>";
        return $table;
    }

    function render(){
        global $wgOut;
        $item = $this->getTable(false);
        $item = $this->processCData($item);
        $wgOut->addHTML($item);
    }
    
    function renderForPDF(){
        global $wgOut;
        $item = $this->getTable(true);
        $item = $this->processCData($item);
        $wgOut->addHTML($item);
    }
}

?>
