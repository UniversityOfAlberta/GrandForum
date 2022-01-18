<?php

class ProjectEdiTab extends AbstractEditableTab {

    var $project;
    var $visibility;

    function ProjectEdiTab($person, $visibility){
        parent::AbstractEditableTab("EDI");
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
        if($me->isRoleAtLeast(STAFF) ||
           $me->isRole(PL, $this->project) || 
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
                    $('#ediAccordion').accordion({autoHeight: false,
                                                     collapsible: true});
                });
            </script>");
        $this->html .= "<div id='ediAccordion'>";
        $year = date('Y', strtotime($this->project->getCreated()) - (3 * 30 * 24 * 60 * 60));
        $today = date('Y', time() - (6 * 30 * 24 * 60 * 60));
        if(isset($_GET['generatePDF'])){
            // Only show the last year in the PDF
            $today = date('Y') - 1;
            $year = $today;
        }
        $phaseDate = $config->getValue('projectPhaseDates');
        $phaseYear = substr($phaseDate[PROJECT_PHASE], 0, 10);
        for($y=$today; $y >= $year; $y--){
            $edi = $this->getBlobData("EDI", $y);
            $this->html .= "<h3><a href='#'>".$y."/".substr($y+1,2,2)."</a></h3>";
            $this->html .= "<div style='overflow: auto;'>{$edi}</div>";
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
                    $('#ediAccordion').accordion({autoHeight: false,
                                                     collapsible: true});
                });
            </script>");
        $this->html .= "<div id='ediAccordion'>";
        $year = date('Y', strtotime($this->project->getCreated()) - (3 * 30 * 24 * 60 * 60));
        $today = date('Y', time() - (6 * 30 * 24 * 60 * 60));
        $phaseDate = $config->getValue('projectPhaseDates');
        $phaseYear = substr($phaseDate[PROJECT_PHASE], 0, 10);
        for($y=$today; $y >= $year; $y--){
            $edi = $this->getBlobData("EDI", $y);
            $this->html .= "<h3><a href='#'>".$y."/".substr($y+1,2,2)."</a></h3>";
            $this->html .= "<div style='overflow: auto;'>";
            $this->html .= "<h3>How do you incorporate aspects of EDI into your research plan and work, and which resources or opportunities are most valuable to you in achieving these goals?</h3>
                            Your answer may include research methodology, group management, training philosophy, personnel hiring, and other initiatives and avenues.
                            <textarea name='edi[$y]' style='height:200px;resize: vertical;'>{$edi}</textarea>";
            $this->html .= "</div>";
        }
        $this->html .= "</div>";
    }
    
    function handleEdit(){
        global $wgOut, $wgUser, $wgRoles, $wgServer, $wgScriptPath, $wgMessage;
        $missing = false;
        $today = date('Y', time() - (6 * 30 * 24 * 60 * 60));
        if(isset($_POST["edi"])){
            foreach($_POST["edi"] as $year => $q){
                if(strlen($q) >= 5 || strlen($q) == 0){ 
                    $this->saveBlobData("EDI", $year, $q);
                }
                else{
                    $missing = true;
                }
            }
        }
        if($missing){
            // Form is incomplete, so an error message, but still keep any changes that were submitted.  Show the form again
            return "Responses must have atleast 5 characters.";
        }
        else{
            Messages::addSuccess("'EDI' updated successfully.");
            redirect("{$this->project->getUrl()}?tab=edi");
        }
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
