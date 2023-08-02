<?php

class ProjectKPISummaryTab extends AbstractTab {

    var $project;
    var $visibility;

    function ProjectKPISummaryTab($project, $visibility){
        parent::AbstractTab("KPI Summary");
        $this->project = $project;
        $this->visibility = $visibility;
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath;
        $this->showKPI();
        return $this->html;
    }

    function showKPI(){
        global $wgServer, $wgScriptPath, $wgUser, $wgOut, $config;
        $me = Person::newFromWgUser();
        
        $summary = ProjectKPITab::getKPITemplate();
        if($me->isMemberOf($this->project) || $this->visibility['isLead']){
        
            $endYear = date('Y', time() - (3 * 30 * 24 * 60 * 60)); // Roll-over kpi in April
            $startYear = date('Y', strtotime($this->project->getCreated()) - (3 * 30 * 24 * 60 * 60));
            
            for($i=$endYear; $i >= $startYear; $i--){
                foreach(array_reverse(ProjectKPITab::$qMap, true) as $q => $quarter){
                    switch($q){
                        case 1:
                            $date = "{$i}-04-01";
                            $enddate = "{$i}-07-01";
                            break;
                        case 2:
                            $date = "{$i}-07-01";
                            $enddate = "{$i}-10-01";
                            break;
                        case 3:
                            $date = "{$i}-10-01";
                            $enddate = ($i+1)."-01-01";
                            break;
                        case 4:
                            $date = ($i+1)."-01-01";
                            $enddate = ($i+1)."-04-01";
                            break;
                    }
                    if(date('Y-m-d') < $date || substr($this->project->getCreated(), 0, 10) > $enddate){
                        continue;
                    }
                    
                    // KPI
                    list($kpi, $md5) = ProjectKPITab::getKPI($this->project, "KPI_{$i}_Q{$q}", $date, $enddate);
                    if($kpi != null){
                        $summary = ProjectKPITab::addKPI($summary, $kpi);
                        break; // Don't check older Quarterly uploads
                    }
                }
            }
            $this->html .= $summary->render();
        }
    }
}    
    
?>
