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
        $project = $this->project;
        $me = Person::newFromId($wgUser->getId());
        
        $this->showKPI();
        
        return $this->html;
    }

    function showKPI(){
        global $wgServer, $wgScriptPath, $wgUser, $wgOut, $config;
        $me = Person::newFromWgUser();
        $project = $this->project;
        
        $structure = @constant(strtoupper(preg_replace("/[^A-Za-z0-9 ]/", '', $config->getValue('networkName'))).'_KPI_STRUCTURE');
        
        $template = file_get_contents("data/GIS KPIs.xlsx");
        $summary = new Budget("XLS", $structure, $template, 1);
        
        if($me->isMemberOf($this->project) || $this->visibility['isLead']){
            $endYear = date('Y', time() - (3 * 30 * 24 * 60 * 60)); // Roll-over kpi in April
            
            $phaseDates = $config->getValue("projectPhaseDates");
            $startYear = date('Y', strtotime($project->getCreated()) - (3 * 30 * 24 * 60 * 60));
            
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
                    if(date('Y-m-d') < $date || substr($project->getCreated(), 0, 10) > $enddate){
                        continue;
                    }
                    
                    // KPI
                    $blb = new ReportBlob(BLOB_EXCEL, 0, 0, $this->project->getId());
                    $addr = ReportBlob::create_address("RP_KPI", "KPI", "KPI_{$i}_Q{$q}", 0);
                    $result = $blb->load($addr, true);
                    $md5 = $blb->getMD5();
                    $xls = $blb->getData();
                    if($xls != null){
                        $kpi = new Budget("XLS", $structure, $xls, 1);
                        
                        foreach($kpi->xls as $key => $row){
                            if(@is_numeric($row[2]->value)){
                                $summary->xls[$key][2]->value += $row[2]->value;
                            }
                        }
                        break; // Don't check older Quarterly uploads
                    }
                }
            }
            
            $summary->xls[69][1]->style .= "white-space: initial;";
            $summary->xls[71][1]->style .= "white-space: initial;";
            $summary->xls[97][1]->style .= "white-space: initial;";
            $summary->xls[98][1]->style .= "white-space: initial;";
            
            $this->html .= $summary->render();
        }
    }
}    
    
?>
