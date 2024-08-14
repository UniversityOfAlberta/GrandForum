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
        global $wgOut, $config, $wgServer, $wgScriptPath;
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
        $today = date('Y');//, time() - (6 * 30 * 24 * 60 * 60));
        if(isset($_GET['generatePDF'])){
            // Only show the last year in the PDF
            $today = date('Y') - 1;
            $year = $today;
        }
        $phaseDate = $config->getValue('projectPhaseDates');
        $phaseYear = substr($phaseDate[PROJECT_PHASE], 0, 10);
        for($y=$today; $y >= $year; $y--){
            $q1 = $this->getBlobData("EDI_Q1", $y, BLOB_ARRAY);
            $q2 = $this->getBlobData("EDI_Q2", $y);
            $q3 = $this->getBlobData("EDI_Q3", $y);
            
            $q1 = implode("<br />", (is_array($q1)) ? $q1 : array());
            
            $this->html .= "<h3><a href='#'>".$y."/".substr($y+1,2,2)."</a></h3>";
            $this->html .= "<div style='overflow: auto;'>
                <h3>Checkmark the resources utilized by you or your FES research group to enhance knowledge of EDI and facilitate its implementation within your FES research group. Refer to the <a href='{$wgServer}{$wgScriptPath}/data/EDI Resource List.pdf' target='_blank'>attached EDI resource list</a> for more information.</h3>
                {$q1}
                
                <h3>List any other resources, including training or workshops, utilized by you or your FES research group members pertaining to EDI here.</h3>
                {$q2}
                
                <h3 style='color:#007c41 !important;'>How do you incorporate aspects of EDI into your HQP hiring strategy and research plan? Include information, if any, regarding collaborations with organizations or initiatives that promote EDI and social justice such as WISEST, ELITE Program for Black Youth, URI, I-STEAM Pathways, etc.</h3>
                {$q3}
            </div>";
        }
        $this->html .= "</div>";
    }
    
    function generateEditBody(){
        global $wgOut, $config, $wgServer, $wgScriptPath;
        if(!$this->canEdit()){
            return;
        }
        $wgOut->addScript("<script type='text/javascript'>
                $(document).ready(function(){
                    $('#ediAccordion').accordion({autoHeight: false,
                                                     collapsible: true});
                });
            </script>");
        $this->html .= "<p>Fill out information for the tab corresponding to the reporting fiscal year (ended on March 31 this year) and <b>not for the ongoing fiscal year</b> (started on April 1 this year).</p>";
        $this->html .= "<div id='ediAccordion'>";
        $year = date('Y', strtotime($this->project->getCreated()) - (3 * 30 * 24 * 60 * 60));
        $today = date('Y');//, time() - (6 * 30 * 24 * 60 * 60));
        $phaseDate = $config->getValue('projectPhaseDates');
        $phaseYear = substr($phaseDate[PROJECT_PHASE], 0, 10);
        for($y=$today; $y >= $year; $y--){
            $q1 = $this->getBlobData("EDI_Q1", $y, BLOB_ARRAY);
            $q2 = $this->getBlobData("EDI_Q2", $y);
            $q3 = $this->getBlobData("EDI_Q3", $y);
            
            $q1 = (is_array($q1)) ? $q1 : array();
            
            $checkboxes1 = new VerticalCheckBox("edi_q1[$y]", "", $q1, array("Gender-based Analysis Plus course",
                                                                             "Indigenous Canada - University of Alberta MOOC",
                                                                             "Teaching beyond the Gender Binary in the University Classroom - Teaching Guide",
                                                                             "Fyrefly Institute",
                                                                             "Race, Research & Policy Portal - IARA Project",
                                                                             "Equity, Diversity, Inclusion Toolkit - WISEST",
                                                                             "Equity, Diversity and Inclusion Module – University of Alberta",
                                                                             "Equity, Diversity, & Inclusivity: University Library Resources"));
            $checkboxes2 = new VerticalCheckBox("edi_q1[$y]", "", $q1, array("Harvard’s Implicit Association Test (IAT)",
                                                                             "NIH Scientific Workforce Diversity Toolkit",
                                                                             "Employing a Diverse Workforce: Making it Work",
                                                                             "Positive Space Initiative: 2SLGBTQI+ Awareness (INC111)",
                                                                             "Anti-Racism Learning Series",
                                                                             "World Diversity in Leadership Conference",
                                                                             "Future Energy Systems EDI Events"));
            
            $this->html .= "<h3><a href='#'>".$y."/".substr($y+1,2,2)."</a></h3>";
            $this->html .= "<div style='overflow: auto;'>";
            $this->html .= "<h3>Checkmark the resources utilized by you or your FES research group to enhance knowledge of EDI and facilitate its implementation within your FES research group. Refer to the <a href='{$wgServer}{$wgScriptPath}/data/EDI Resource List.pdf' target='_blank'>attached EDI resource list</a> for more information.</h3>
                            <input type='hidden' name='edi_q1[$y][]' />
                            <div style='display:flex'>
                                <div style='margin-right:15px;'>{$checkboxes1->render()}</div>
                                <div>{$checkboxes2->render()}</div>
                            </div>
                
                            <h3>List any other resources, including training or workshops, utilized by you or your FES research group members pertaining to EDI here.</h3>
                            <textarea name='edi_q2[$y]' style='height:200px;resize: vertical;'>{$q2}</textarea>
                
                            <h3 style='color:#007c41 !important;'>How do you incorporate aspects of EDI into your HQP hiring strategy and research plan? Include information, if any, regarding collaborations with organizations or initiatives that promote EDI and social justice such as WISEST, ELITE Program for Black Youth, URI, I-STEAM Pathways, etc.</h3>
                            <textarea name='edi_q3[$y]' style='height:200px;resize: vertical;'>{$q3}</textarea>";
            $this->html .= "</div>";
        }
        $this->html .= "</div>";
    }
    
    function handleEdit(){
        global $wgOut, $wgUser, $wgRoles, $wgServer, $wgScriptPath, $wgMessage;
        $missing = false;
        $today = date('Y', time() - (6 * 30 * 24 * 60 * 60));
        if(isset($_POST["edi_q1"])){
            foreach($_POST["edi_q1"] as $year => $q){
                $q = array_filter($q);
                $this->saveBlobData("EDI_Q1", $year, $q, BLOB_ARRAY);
                $this->saveBlobData("EDI_Q2", $year, $_POST["edi_q2"][$year]);
                $this->saveBlobData("EDI_Q3", $year, $_POST["edi_q3"][$year]);
            }
        }
        Messages::addSuccess("'EDI' updated successfully.");
        redirect("{$this->project->getUrl()}?tab=edi");
    }
    
    function saveBlobData($blobItem, $year, $value, $blobType=BLOB_TEXT){
        $value = str_replace(">", "&gt;", 
                 str_replace("<", "&lt;", $value));
        $blb = new ReportBlob($blobType, $year, 0, $this->project->getId());
        $addr = ReportBlob::create_address("RP_PROJECT_REPORT", "REPORT", $blobItem, 0);
        $blb->store($value, $addr);
    }
    
    function getBlobData($blobItem, $year, $blobType=BLOB_TEXT){
        $blb = new ReportBlob($blobType, $year, 0, $this->project->getId());
        $addr = ReportBlob::create_address("RP_PROJECT_REPORT", "REPORT", $blobItem, 0);
        $result = $blb->load($addr);
        return $blb->getData();
    }
    
    function canEdit(){
        return (!$this->project->isFeatureFrozen(FREEZE_EDI) && $this->userCanView());
    }
    
}
?>
