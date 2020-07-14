<?php

class ProjectRosterReportItem extends StaticReportItem {

    function render(){
        global $wgOut;
        $project = Project::newFromHistoricId($this->projectId);
        $subs = $project->getSubProjects();

        $item = "";
        $dashboards = array();
        $header = null;
        foreach($subs as $sub){
            $dashboard1 = new DashboardTable(PROJECT_CHAMP_ROSTER_STRUCTURE, $sub);
            $dashboard2 = new DashboardTable(PROJECT_NI_ROSTER_STRUCTURE, $sub);
            if(count($sub->getLeaders()) == 0){
                $head = $dashboard2->copy()->where(HEAD);
                $dashboard2->filter(HEAD);
                $blankDash = new DashboardTable(array(array(BLANK, BLANK)),
                                                array(array(new BlankCell(BLANK, "", "", "", "", ""), new BlankCell(BLANK, "", "", "", "", ""))),
                                                null);
                $dashboard2 = DashboardTable::union_tables(array($head, $blankDash, $dashboard2));
            }
            $joined = $dashboard1->join($dashboard2->copy(), true);
            $header = $joined->copy()->where(HEAD);
            $joined->filter(HEAD);
            $dashboards[] = $joined;
            $joined->xls[1][0]->span = 2;
            $joined->xls[1][1]->style .= "background:#DDDDDD;";
            $joined->xls[1][2]->style .= "background:#DDDDDD;";
        }
        $dash = DashboardTable::union_tables(array_merge(array($header), $dashboards));
        if($dash != null){
            $item = $dash->render(true, false);
        }
        $item = $this->processCData($item);
        $wgOut->addHTML($item);
    }
    
    function renderForPDF(){
        global $wgOut;
        $project = Project::newFromHistoricId($this->projectId);
        $subs = $project->getSubProjects();

        $item = "";
        $dashboards = array();
        $header = null;
        foreach($subs as $sub){
            $dashboard1 = new DashboardTable(PROJECT_CHAMP_ROSTER_STRUCTURE, $sub);
            $dashboard2 = new DashboardTable(PROJECT_NI_ROSTER_STRUCTURE, $sub);
            if(count($sub->getLeaders()) == 0){
                $head = $dashboard2->copy()->where(HEAD);
                $dashboard2->filter(HEAD);
                $blankDash = new DashboardTable(array(array(BLANK, BLANK)),
                                                array(array(new BlankCell(BLANK, "", "", "", "", ""), new BlankCell(BLANK, "", "", "", "", ""))),
                                                null);
                $dashboard2 = DashboardTable::union_tables(array($head, $blankDash, $dashboard2));
            }
            $joined = $dashboard1->join($dashboard2->copy(), true);
            $header = $joined->copy()->where(HEAD);
            $joined->filter(HEAD);
            $joined->xls[1][0]->span = 2;
            $joined->xls[1][1]->style .= "background:#DDDDDD;";
            $joined->xls[1][2]->style .= "background:#DDDDDD;";
            $joined->xls[1][2]->span = 1;
            $dashboards[] = $joined;
        }
        $dash = DashboardTable::union_tables(array_merge(array($header), $dashboards));
        if($dash != null){
            $item = $dash->renderForPDF(true, false);
            $item = str_replace("<br />", "&nbsp;", $item);
            $item = str_replace("class=\"smaller\"", "class=\"small\"", $item);
            $item = str_replace("><b>Champions</b>", "width='15%'><b>Champions</b>", $item);
            $item = str_replace("><b>NIs</b>", "width='17%'><b>NIs</b>", $item);
            $item = str_replace("><b>Affiliation</b>", "width='34%'><b>Affiliation</b>", $item);
            $item = str_replace("page-break-inside:avoid;", "", $item);
            $item = str_replace("white-space:nowrap;", "", $item);
        }
        $item = $this->processCData($item);
        $wgOut->addHTML($item);
    }
}

?>
