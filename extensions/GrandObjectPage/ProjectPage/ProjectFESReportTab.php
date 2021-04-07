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
                    $('#reportAccordion').accordion({autoHeight: false,
                                                     collapsible: true});
                });
            </script>");
        $this->html .= "<div id='reportAccordion'>";
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
            $q1 = $this->getBlobData("Q1", $y);
            $q2 = $this->getBlobData("Q2", $y);
            $q3 = $this->getBlobData("Q3", $y);
            $q4 = $this->getBlobData("Q4", $y);
            $q5 = $this->getBlobData("Q5", $y);
            $q6 = $this->getBlobData("Q6", $y);
            $q7 = $this->getBlobData("Q7", $y);
            $q8 = $this->getBlobData("Q8", $y);
            $q9 = $this->getBlobData("Q9", $y);
            
            $this->html .= "<h3><a href='#'>".$y."/".substr($y+1,2,2)."</a></h3>";
            $this->html .= "<div style='overflow: auto;'>";
            $this->html .= "<h3>1. Explain the research progress made during the past fiscal year (Apr$y-Mar".($y+1).") towards achieving your project’s overarching objectives. Which of your short term objectives were met? Focus your answers on research (not administrative progress such as personnel hiring).</h3>
                            {$q1}
                            
                            <h3>2. What challenges did you face over the past fiscal year (Apr$y-Mar".($y+1).") in addressing your research objectives, and what measures did you adopt in response to these challenges?</h3>
                            {$q4}
                            
                            <h3>3. What specific research objectives do you plan to complete during the current fiscal year (Apr".($y+1)."-Mar".($y+2)."), and how will these help you advance in achieving your overarching objectives?</h3>
                            {$q5}
                            
                            <h3>4. Did you modify your project milestones for the current fiscal year (Apr".($y+1)."-Mar".($y+2).") and any future years? If so, provide a rationale that supports the changes made.</h3>
                            {$q2}
                            
                            <h3>5. Explain how equity, diversity, and inclusivity have been considered in the following aspects, and mention specific issues in your research field as applicable:</h3>
                            <h4>Ongoing design and implementation of your research activities</h4>
                            {$q3}
                            <h4>Composition of the project team</h4>
                            {$q8}
                            <h4>Formation of the training plan for your HQP</h4>
                            {$q9}
                            
                            <h3>6. Have you or your team attended any EDI events during the past fiscal year (Apr$y-Mar".($y+1).")? If so, list them and indicate who attended from your team.</h3>
                            {$q6}
                            
                            <h3>7. Are there any resources or opportunities that would help to better support EDI within your team?</h3>
                            {$q7}
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
            $q4 = $this->getBlobData("Q4", $y);
            $q5 = $this->getBlobData("Q5", $y);
            $q6 = $this->getBlobData("Q6", $y);
            $q7 = $this->getBlobData("Q7", $y);
            $q8 = $this->getBlobData("Q8", $y);
            $q9 = $this->getBlobData("Q9", $y);
            
            $this->html .= "<h3><a href='#'>".$y."/".substr($y+1,2,2)."</a></h3>";
            $this->html .= "<div style='overflow: auto;'>";
            $this->html .= "<h3>1. Explain the research progress made during the past fiscal year (Apr$y-Mar".($y+1).") towards achieving your project’s overarching objectives. Which of your short term objectives were met? Focus your answers on research (not administrative progress such as personnel hiring). <small>(300 words)</small></h3>
                            <textarea name='report_q1[$y]' style='height:200px;resize: vertical;'>{$q1}</textarea>
                            
                            <h3>2. What challenges did you face over the past fiscal year (Apr$y-Mar".($y+1).") in addressing your research objectives, and what measures did you adopt in response to these challenges? <small>(250 words)</small></h3>
                            <textarea name='report_q4[$y]' style='height:200px;resize: vertical;'>{$q4}</textarea>
                            
                            <h3>3. What specific research objectives do you plan to complete during the current fiscal year (Apr".($y+1)."-Mar".($y+2)."), and how will these help you advance in achieving your overarching objectives? <small>(250 words)</small></h3>
                            <textarea name='report_q5[$y]' style='height:200px;resize: vertical;'>{$q5}</textarea>
                            
                            <h3>4. Did you modify your project milestones for the current fiscal (Apr".($y+1)."-Mar".($y+2).") year and any future years? If so, provide a rationale that supports the changes made. <small>(250 words)</small></h3>
                            <textarea name='report_q2[$y]' style='height:200px;resize: vertical;'>{$q2}</textarea>
                            
                            <h3>5. Explain how equity, diversity, and inclusivity have been considered in the following aspects, and mention specific issues in your research field as applicable:</h3>
                            <h4>Ongoing design and implementation of your research activities</h4>
                            <textarea name='report_q3[$y]' style='height:100px;resize: vertical;'>{$q3}</textarea>
                            <h4>Composition of the project team</h4>
                            <textarea name='report_q8[$y]' style='height:100px;resize: vertical;'>{$q8}</textarea>
                            <h4>Formation of the training plan for your HQP</h4>
                            <textarea name='report_q9[$y]' style='height:100px;resize: vertical;'>{$q9}</textarea>
                            
                            <h3>6. Have you or your team attended any EDI events during the past fiscal year (Apr$y-Mar".($y+1).")? If so, list them and indicate who attended from your team.</h3>
                            <textarea name='report_q6[$y]' style='height:200px;resize: vertical;'>{$q6}</textarea>
                            
                            <h3>7. Are there any resources or opportunities that would help to better support EDI within your team?</h3>
                            <textarea name='report_q7[$y]' style='height:200px;resize: vertical;'>{$q7}</textarea>
            ";
            $this->html .= "</div>";
        }
        $this->html .= "</div>";
    }
    
    function handleEdit(){
        global $wgOut, $wgUser, $wgRoles, $wgServer, $wgScriptPath, $wgMessage;
        $missing = false;
        $today = date('Y', time() - (6 * 30 * 24 * 60 * 60));
        for($i = 1; $i <= 9; $i++){
            if(isset($_POST["report_q$i"])){
                foreach($_POST["report_q$i"] as $year => $q){
                    $this->saveBlobData("Q$i", $year, $q);
                    if($q == "" && $year == $today){
                        $missing = true;
                    }
                }
            }
        }
        if($missing){
            // Form is incomplete, so an error message, but still keep any changes that were submitted.  Show the form again
            return "Not all Reporting questions have been answered.  Make sure there are responses for each question.";
        }
        else{
            Messages::addSuccess("'Reporting' updated successfully.");
            redirect("{$this->project->getUrl()}?tab=reporting");
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
