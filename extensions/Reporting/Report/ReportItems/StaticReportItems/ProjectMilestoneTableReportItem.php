<?php

class ProjectMilestoneTableReportItem extends StaticReportItem {

    function render(){
        global $wgOut;
        $project = Project::newFromHistoricId($this->projectId);
        $date = $this->getAttr("date", false);
        $date = ($date == "") ? false : $date;
        if($project != null){
            $tab = new ProjectMilestonesTab($project, array('edit' => 0));
            $tab->showMilestones(false, $date);
            $item = $tab->html;
            $item = $this->processCData($item);
            $wgOut->addHTML($item);
        }
    }
    
    function renderForPDF(){
        global $wgOut;
        $project = Project::newFromHistoricId($this->projectId);
        $date = $this->getAttr("date", "");
        $date = ($date == "") ? false : $date;
        if($project != null){
            $tab = new ProjectMilestonesTab($project, array('edit' => 0));
            $tab->showMilestones(true, $date);
            $item = $tab->html;
            $item = $this->processCData($item);
            $wgOut->addHTML($item);
        }
    }
}

?>
