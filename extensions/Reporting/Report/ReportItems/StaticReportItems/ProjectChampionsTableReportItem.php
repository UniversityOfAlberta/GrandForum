<?php

class ProjectChampionsTableReportItem extends StaticReportItem {

    private function getTable(){
        $project = Project::newFromId($this->projectId);
        $subs = $project->getSubProjects();
        $table = "";
        $table .= "<table class='dashboard' width='100%' style='width:100%;background-color:#000000;border-color:#000000;margin-bottom:15px;border-spacing:".max(1, (0.5*DPI_CONSTANT))."px;'>
            <tr><th></th><th></th><th></th>";
        foreach($subs as $sub){
            $table .= "<th class='small' style='display:table-cell;'>{$sub->getName()}</th>";
        }
        $table .= "<th></th></tr>
                   <tr><th style='background: #DDDDDD;' align='right'>First</th><th style='background: #DDDDDD;' align='left'>Last</th><th style='background: #DDDDDD;'>Organization</th>";
        foreach($subs as $sub){
            $count = count($sub->getChampions());
            $table .= "<th style='background: #DDDDDD;'>{$count}</th>";
        }
        $table .= "<th style='background: #DDDDDD;'></th></tr>";
        foreach($project->getChampions() as $c){
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
            
            $table .= "<tr>";
            $table .= "<td align='right'>{$champion->getFirstName()}</td>";
            $table .= "<td align='left'>{$champion->getLastName()}</td>";
            $table .= "<td>{$org}</td>";
            $count = 0;
            foreach($subs as $sub){
                if($champion->isMemberOf($sub)){
                    $count++;
                    $time = $champion->getTimeOnProject($sub, "%m");
                    if($time == 0){
                        $time = 1;
                    }
                    $table .= "<td align='center'>{$time}</td>";
                }
                else{
                    $table .= "<td></td>";
                }
            }
            $table .= "<td align='center' style='background: #DDDDDD;font-weight:bold;'>{$count}</td></tr>";
        }
        $table .= "</table>";
        $table .= "<small>
            <ul>
                <li>The numbers in the middle of the table represent the number of months that the Champion has been with the project.</li>
                <li>The numbers on the top edge represent the number of Champions are on each Project.</li>
                <li>The numbers on the right edge represent the number of Sub-Projects the Champion is on.</li>
            </ul>
            </small>";
        return $table;
    }

    function render(){
        global $wgOut;
        $item = $this->getTable();
        $item = $this->processCData($item);
        $wgOut->addHTML($item);
    }
    
    function renderForPDF(){
        global $wgOut;
        $item = $this->getTable();
        $item = $this->processCData($item);
        $wgOut->addHTML($item);
    }
}

?>
