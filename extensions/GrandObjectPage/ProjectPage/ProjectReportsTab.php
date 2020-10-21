<?php

class ProjectReportsTab extends AbstractTab {

    var $project;
    var $visibility;

    function ProjectReportsTab($project, $visibility){
        parent::AbstractTab("Reports");
        $this->project = $project;
        $this->visibility = $visibility;
    }
    
    function generateBody(){
        global $config, $wgUser, $wgServer, $wgScriptPath;
        $project = $this->project;
        $me = Person::newFromId($wgUser->getId());
        
        if(!$me->isRole(CI, $project) &&
           !$me->isRole(PL, $project) &&
           !$me->isRoleAtLeast(STAFF)){
            return;
        }
        
        $endYear = date('Y', time() - (3 * 30 * 24 * 60 * 60));
        if($project->deleted){
            $startYear = substr($project->getDeleted(), 0, 4)-1;
        }
        
        $phaseDates = $config->getValue("projectPhaseDates");
        $startYear = max(substr($phaseDates[1], 0, 4), date('Y', strtotime($project->getCreated()) - (3 * 30 * 24 * 60 * 60)));
        
        $reports = array("Progress Report" => RP_PROGRESS,
                         "Milestones Report" => 'RP_MILE_REPORT');
        
        $this->html .= "<ul>";
        for($i=$endYear; $i >= $startYear; $i--){
            $repHTML = array();
            foreach($reports as $label => $report){
                $report = new DummyReport($report, $me, $project, $i);
                $report->year = $i;
                
                $pdf = $report->getPDF();
                if((count($pdf) > 0)){
                    $pdfButton = "<a href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf={$pdf[0]['token']}'>{$label}</a>";
                    $pdfDate = "{$pdf[0]['timestamp']}";
                    $repHTML[] = $pdfButton;
                }
            }
            if(count($repHTML) > 0){
                $this->html .= "<li><b>{$i}</b>: ".implode(", ", $repHTML)."</li>";
            }
        }
        $this->html .= "</ul>";
        
        return $this->html;
    }
}    
    
?>
