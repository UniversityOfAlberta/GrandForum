<?php

class ProjectFESReportTab extends AbstractEditableTab {

    var $project;
    var $visibility;

    function ProjectFESReportTab($person, $visibility){
        parent::AbstractEditableTab("Reporting");
        $this->project = $person;
        $this->visibility = $visibility;
    }
    
    function userCanView(){
        $me = Person::newFromWgUser();
        // Check that they are leader
        if($me->leadershipOf($this->project) || $me->isRoleAtLeast(STAFF)){
            return true;
        }
    }

    function generateBody(){
        global $wgOut, $config;
        if(!$this->userCanView()){
            return;
        }
        $wgOut->addScript("<script type='text/javascript'>
                $(document).ready(function(){
                    $('#reportAccordion').accordion({autoHeight: false,
                                                     collapsible: true});
                });
            </script>");
        $this->html .= "<div id='reportAccordion'>";
        $year = date('Y', strtotime($this->project->getCreated()) - (3 * 30 * 24 * 60 * 60));
        $today = date('Y', time() - (3 * 30 * 24 * 60 * 60));
        $phaseDate = $config->getValue('projectPhaseDates');
        $phaseYear = substr($phaseDate[PROJECT_PHASE], 0, 10);
        for($y=$today; $y >= $year; $y--){
            // Q1
            $blb = new ReportBlob(BLOB_TEXT, $y, 0, $this->project->getId());
            $addr = ReportBlob::create_address("RP_PROJECT_REPORT", "REPORT", 'Q1', 0);
            $result = $blb->load($addr);
            $q1 = nl2br($blb->getData());
            
            // Q2
            $blb = new ReportBlob(BLOB_TEXT, $y, 0, $this->project->getId());
            $addr = ReportBlob::create_address("RP_PROJECT_REPORT", "REPORT", 'Q2', 0);
            $result = $blb->load($addr);
            $q2 = nl2br($blb->getData());
            
            // Q3
            $blb = new ReportBlob(BLOB_TEXT, $y, 0, $this->project->getId());
            $addr = ReportBlob::create_address("RP_PROJECT_REPORT", "REPORT", 'Q3', 0);
            $result = $blb->load($addr);
            $q3 = nl2br($blb->getData());
            
            $this->html .= "<h3><a href='#'>".$y."/".substr($y+1,2,2)."</a></h3>";
            $this->html .= "<div style='overflow: auto;'>";
            $this->html .= "<h3>Provide a brief description of your research progress during FY".($y - $phaseYear + 2)."</h3>
                            {$q1}
                            
                            <h3>Did you change your milestones for this year and moving forward? If so, why?</h3>
                            {$q2}
                            
                            <h3>What steps did you take to ensure equity, diversity and inclusion (EDI) within your team?</h3>
                            {$q3}
            ";
            $this->html .= "</div>";
        }
        $this->html .= "</div>";
    }
    
    function generateEditBody(){
        global $wgOut, $config;
        if(!$this->canEdit()){
            return;
        }
        $wgOut->addScript("<script type='text/javascript'>
                $(document).ready(function(){
                    $('#reportAccordion').accordion({autoHeight: false,
                                                     collapsible: true});
                });
            </script>");
        $this->html .= "<div id='reportAccordion'>";
        $year = date('Y', strtotime($this->project->getCreated()) - (3 * 30 * 24 * 60 * 60));
        $today = date('Y', time() - (3 * 30 * 24 * 60 * 60));
        $phaseDate = $config->getValue('projectPhaseDates');
        $phaseYear = substr($phaseDate[PROJECT_PHASE], 0, 10);
        for($y=$today; $y >= $year; $y--){
            // Q1
            $blb = new ReportBlob(BLOB_TEXT, $y, 0, $this->project->getId());
            $addr = ReportBlob::create_address("RP_PROJECT_REPORT", "REPORT", 'Q1', 0);
            $result = $blb->load($addr);
            $q1 = $blb->getData();
            
            // Q2
            $blb = new ReportBlob(BLOB_TEXT, $y, 0, $this->project->getId());
            $addr = ReportBlob::create_address("RP_PROJECT_REPORT", "REPORT", 'Q2', 0);
            $result = $blb->load($addr);
            $q2 = $blb->getData();
            
            // Q3
            $blb = new ReportBlob(BLOB_TEXT, $y, 0, $this->project->getId());
            $addr = ReportBlob::create_address("RP_PROJECT_REPORT", "REPORT", 'Q3', 0);
            $result = $blb->load($addr);
            $q3 = $blb->getData();
            
            $this->html .= "<h3><a href='#'>".$y."/".substr($y+1,2,2)."</a></h3>";
            $this->html .= "<div style='overflow: auto;'>";
            $this->html .= "<h3>Provide a brief description of your research progress during FY".($y - $phaseYear + 2)." <small>(300 words)</small></h3>
                            Please focus on scholarly and not administrative activities
                            <textarea name='report_q1[$y]' style='height:200px;resize: vertical;'>{$q1}</textarea>
                            
                            <h3>Did you change your milestones for this year and moving forward? If so, why? <small>(300 words)</small></h3>
                            <textarea name='report_q2[$y]' style='height:200px;resize: vertical;'>{$q2}</textarea>
                            
                            <h3>What steps did you take to ensure equity, diversity and inclusion (EDI) within your team?</h3>
                            <small>Suggested topics to address<br />
                                <ul>
                                   <li>What are the EDI issues in your field?</li>
                                   <li>How are you using this project to address them?</li>
                                   <li>Have you or any of your team members attended EDI related events/workshops during the past year?  If yes, please provide the event title(s).</li>
                                </ul>
                            </small>
                            <textarea name='report_q3[$y]' style='height:200px;resize: vertical;'>{$q3}</textarea>
            ";
            $this->html .= "</div>";
        }
        $this->html .= "</div>";
    }
    
    function handleEdit(){
        global $wgOut, $wgUser, $wgRoles, $wgServer, $wgScriptPath;
        if(isset($_POST['report_q1'])){
            foreach($_POST['report_q1'] as $year => $q){
                $q = str_replace(">", "&gt;", 
                     str_replace("<", "&lt;", $q));
                $blb = new ReportBlob(BLOB_TEXT, $year, 0, $this->project->getId());
                $addr = ReportBlob::create_address("RP_PROJECT_REPORT", "REPORT", 'Q1', 0);
                $blb->store($q, $addr);
            }
        }
        if(isset($_POST['report_q2'])){
            foreach($_POST['report_q2'] as $year => $q){
                $q = str_replace(">", "&gt;", 
                     str_replace("<", "&lt;", $q));
                $blb = new ReportBlob(BLOB_TEXT, $year, 0, $this->project->getId());
                $addr = ReportBlob::create_address("RP_PROJECT_REPORT", "REPORT", 'Q2', 0);
                $blb->store($q, $addr);
            }
        }
        if(isset($_POST['report_q3'])){
            foreach($_POST['report_q3'] as $year => $q){
                $q = str_replace(">", "&gt;", 
                     str_replace("<", "&lt;", $q));
                $blb = new ReportBlob(BLOB_TEXT, $year, 0, $this->project->getId());
                $addr = ReportBlob::create_address("RP_PROJECT_REPORT", "REPORT", 'Q3', 0);
                $blb->store($q, $addr);
            }
        }
        header("Location: {$this->project->getUrl()}?tab=reporting");
        exit;
    }
    
    function canEdit(){
        return ($this->userCanView());
    }
    
}
?>
