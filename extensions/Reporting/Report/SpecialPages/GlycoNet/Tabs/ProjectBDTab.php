<?php

class ProjectBDTab extends AbstractTab {

    var $project;
    var $rp;
    var $title;

    function ProjectBDTab($project){
        parent::AbstractTab("Business Development");
        $this->project = $project;
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath, $config;
        $this->html .= "<table id='bd_table' class='wikitable' width='100%'>
                            <thead>
                                <tr>
                                    <th rowspan='3'>Grant Type</th>
                                    <th rowspan='3'>Research Theme</th>
                                    <th rowspan='3'>Project Code</th>
                                    <th rowspan='3'>Lead NI</th>
                                    <th rowspan='3'>Product Type</th>
                                    <th colspan='5'>Proof of Concept</th>
                                    <th colspan='4'>Preclinical</th>
                                    <th rowspan='3'>Targets / Indication</th>
                                    <th rowspan='3'>where are they at ,  important milestone</th>
                                    <th rowspan='3'>IP Filing</th>
                                    <th rowspan='3'>next step</th>
                                </tr>
                                <tr>
                                    <th>Target Validation</th>
                                    <th>Candidate Generation / Screening</th>
                                    <th>In vitro/ ex-vivo</th>
                                    <th>In vivo</th>
                                    <th>Candidate Selected</th>
                                    <th>Large Scale Syntheis</th>
                                    <th>Efficacy</th>
                                    <th>Toxicology</th>
                                    <th>Pharmacology</th>
                                </tr>
                                <tr>
                                    <th>1</th>
                                    <th>2</th>
                                    <th>3</th>
                                    <th>4</th>
                                    <th>5</th>
                                    <th>1</th>
                                    <th>2</th>
                                    <th>3</th>
                                    <th>4</th>
                                </tr>
                            </thead>
                            <tbody>";
        $leaders = array();
        foreach($this->project->getLeaders() as $leader){
            $leaders[] = "<a href='{$leader->getUrl()}'>{$leader->getNameForForms()}</a>";
        }
        $this->html .= "<tr>";
        $this->html .= "<td>{$this->getBlobValue('GRANT_TYPE')}</td>";
        $this->html .= "<td>{$this->project->getChallenge()->getAcronym()}</td>";
        $this->html .= "<td>{$this->project->getName()}</td>";
        $this->html .= "<td>".implode(", ", $leaders)."</td>";
        $this->html .= "<td>{$this->getBlobValue('PRODUCT_TYPE')}</td>";
        $this->html .= "<td class='progress' align='center'>{$this->getBlobValue('PROOF_1')}</td>";
        $this->html .= "<td class='progress' align='center'>{$this->getBlobValue('PROOF_2')}</td>";
        $this->html .= "<td class='progress' align='center'>{$this->getBlobValue('PROOF_3')}</td>";
        $this->html .= "<td class='progress' align='center'>{$this->getBlobValue('PROOF_4')}</td>";
        $this->html .= "<td class='progress' align='center'>{$this->getBlobValue('PROOF_5')}</td>";
        $this->html .= "<td class='progress' align='center'>{$this->getBlobValue('PRE_1')}</td>";
        $this->html .= "<td class='progress' align='center'>{$this->getBlobValue('PRE_2')}</td>";
        $this->html .= "<td class='progress' align='center'>{$this->getBlobValue('PRE_3')}</td>";
        $this->html .= "<td class='progress' align='center'>{$this->getBlobValue('PRE_4')}</td>";
        $this->html .= "<td>{$this->getBlobValue('TARGETS')}</td>";
        $this->html .= "<td>".nl2br($this->getBlobValue('MILESTONE'))."</td>";
        $this->html .= "<td>{$this->getBlobValue('IP')}</td>";
        $this->html .= "<td>".nl2br($this->getBlobValue('NEXT'))."</td>";
        $this->html .= "</tr>";
        $this->html .= "</tbody></table>";
        $this->html .= "<script type='text/javascript'>
            $('.progress').each(function(){
                var val = $(this).text();
                if(val == 'In Progress'){
                    $(this).closest('td').css('background-color', 'blue');
                    $(this).closest('td').css('color', 'white');
                }
                else if(val == 'Completed'){
                    $(this).closest('td').css('background-color', 'green');
                    $(this).closest('td').css('color', 'white');
                }
                else {
                    $(this).closest('td').css('background-color', '');
                    $(this).closest('td').css('color', 'black');
                }
            });
        </script>";
    }
    
    function getBlobValue($blobItem){
        $year = 0; // Don't have a year so that it remains the same each year
        $personId = 0;
        $projectId = $this->project->getId();
        
        $blb = new ReportBlob(BLOB_RAW, $year, $personId, $projectId);
        $addr = ReportBlob::create_address("RP_BD_REPORT", "BD", $blobItem, 0);
        $result = $blb->load($addr);
        return $blb->getData();
    }

}    
    
?>
