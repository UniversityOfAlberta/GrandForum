<?php

class ProjectRosterReportItem extends StaticReportItem {

    function render(){
        global $wgOut;
        $project = Project::newFromId($this->projectId);
        $subs = $project->getSubProjects();

        $item = "";
        $dashboards = array();
        $header = null;
        foreach($subs as $sub){
            $dashboard = new DashboardTable(PROJECT_ROSTER_STRUCTURE, $sub);
            $header = $dashboard->copy()->where(HEAD);
            $dashboard->filter(HEAD);
            $dashboards[] = $dashboard;
        }
        $dash = DashboardTable::union_tables(array_merge($header, $dashboards));
        $item = $dash->render(true, false);
        $item = $this->processCData($item);
        $wgOut->addHTML($item);
    }
    
    function renderForPDF(){
        global $wgOut;
        $project = Project::newFromId($this->projectId);
        $subs = $project->getSubProjects();

        $item = "";
        $dashboards = array();
        $header = null;
        foreach($subs as $sub){
            $dashboard = new DashboardTable(PROJECT_ROSTER_STRUCTURE, $sub);
            $header = $dashboard->copy()->where(HEAD);
            $dashboard->filter(HEAD);
            $dashboards[] = $dashboard;
        }
        $dash = DashboardTable::union_tables(array_merge(array($header), $dashboards));
        $item = $dash->renderForPDF(true, false);
        $item = $this->processCData($item);
        $wgOut->addHTML($item);
    }
}

?>
