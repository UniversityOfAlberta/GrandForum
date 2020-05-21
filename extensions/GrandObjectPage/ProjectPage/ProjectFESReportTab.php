<?php

class ProjectFESReportTab extends AbstractEditableTab {

    var $project;
    var $visibility;

    function ProjectFESReportTab($person, $visibility){
        parent::AbstractEditableTab("Reporting");
        $this->project = $person;
        $this->visibility = $visibility;
    }
    
    function generatePDFBody(){
        $this->generateBody();
    }
    
    function canGeneratePDF(){
        return true;
    }
    
    function userCanView(){
        $me = Person::newFromWgUser();
        // Check that they are leader
        if($me->leadershipOf($this->project) || 
           $me->isRoleAtLeast(STAFF) ||
           $me->isRole(PA, $this->project)){
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
        $today = date('Y', time() - (6 * 30 * 24 * 60 * 60));
        if(isset($_GET['generatePDF'])){
            // Only show the last year in the PDF
            $today = date('Y');
            $year = $today;
        }
        $phaseDate = $config->getValue('projectPhaseDates');
        $phaseYear = substr($phaseDate[PROJECT_PHASE], 0, 10);
        for($y=$today; $y >= $year; $y--){
            $q1 = $this->getBlobData("Q1", $y);
            $q2 = $this->getBlobData("Q2", $y);
            $q3 = $this->getBlobData("Q3", $y);
            
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
        $today = date('Y', time() - (6 * 30 * 24 * 60 * 60));
        $phaseDate = $config->getValue('projectPhaseDates');
        $phaseYear = substr($phaseDate[PROJECT_PHASE], 0, 10);
        for($y=$today; $y >= $year; $y--){
            $q1 = $this->getBlobData("Q1", $y);
            $q2 = $this->getBlobData("Q2", $y);
            $q3 = $this->getBlobData("Q3", $y);
            
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
                $this->saveBlobData("Q1", $year, $q);
            }
        }
        if(isset($_POST['report_q2'])){
            foreach($_POST['report_q2'] as $year => $q){
                $this->saveBlobData("Q2", $year, $q);
            }
        }
        if(isset($_POST['report_q3'])){
            foreach($_POST['report_q3'] as $year => $q){
                $this->saveBlobData("Q3", $year, $q);
            }
        }
        header("Location: {$this->project->getUrl()}?tab=reporting");
        exit;
    }
    
    function saveBlobData($blobItem, $year, $value){
        $value = str_replace(">", "&gt;", 
                 str_replace("<", "&lt;", $value));
        $blb = new ReportBlob(BLOB_TEXT, $year, 0, $this->project->getId());
        $addr = ReportBlob::create_address("RP_PROJECT_REPORT", "REPORT", $blobItem, 0);
        $blb->store($value, $addr);
    }
    
    function getBlobData($blobItem, $year){
        $blb = new ReportBlob(BLOB_TEXT, $year, 0, $this->project->getId());
        $addr = ReportBlob::create_address("RP_PROJECT_REPORT", "REPORT", $blobItem, 0);
        $result = $blb->load($addr);
        return $blb->getData();
    }
    
    function canEdit(){
        return ($this->userCanView());
    }
    
}
?>
