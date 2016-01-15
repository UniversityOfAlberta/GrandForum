<?php

class ProjectMilestoneTableReportItem extends StaticReportItem {

    function render(){
        global $wgOut;
        $project = Project::newFromId($this->projectId);
        $tab = new ProjectMilestonesTab($project, array());
        $tab->showMilestones();
        $item = $tab->html;
        $item = $this->processCData($item);
        $wgOut->addHTML($item);
    }
    
    function renderForPDF(){
        global $wgOut;
        $project = Project::newFromId($this->projectId);
        $tab = new ProjectMilestonesTab($project, array());
        $tab->showMilestones();
        $item = $tab->html;
        $item = $this->processCData($item);
        $wgOut->addHTML($item);
    }
}

?>
