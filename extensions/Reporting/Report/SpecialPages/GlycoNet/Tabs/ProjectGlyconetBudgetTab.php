<?php

class ProjectGlyconetBudgetTab extends AbstractTab {

    var $project;

    function ProjectGlyconetBudgetTab($project){
        parent::AbstractTab("Budget");
        $this->project = $project;
    }
    
    function handleEdit(){
        global $config, $wgMessage;
        if(isset($_FILES["file_{$this->id}"]) && $_FILES["file_{$this->id}"]['name'] != ""){
            $name = $_FILES["file_{$this->id}"]['name'];
            $tmp = $_FILES["file_{$this->id}"]['tmp_name'];
            $contents = file_get_contents($tmp);
            $blb = new ReportBlob(BLOB_EXCEL, 0, 0, $this->project->getId());
            $addr = ReportBlob::create_address(RP_LEADER, LDR_BUDGET, LDR_BUD_ALLOC, 0);
            $blb->store($contents, $addr);
        }
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath;
        $project = $this->project;
        $me = Person::newFromId($wgUser->getId());
        $this->html .= "<p>
                            <b>Upload Budget:</b>
                            <input type='file' name='file_{$this->id}' accept='.xls,.xlsx' />
                            <input type='submit' name='submit' value='Upload' />
                        </p>";
        if(isset($_POST['submit'])){
            $this->handleEdit();
        }
        
        $this->showBudget();
        
        return $this->html;
    }

    function showBudget(){
        global $wgServer, $wgScriptPath, $wgUser, $wgOut, $config;
        $me = Person::newFromWgUser();
        $project = $this->project;

        $blb = new ReportBlob(BLOB_EXCEL, 0, 0, $this->project->getId());
        $addr = ReportBlob::create_address(RP_LEADER, LDR_BUDGET, LDR_BUD_ALLOC, 0);
        $result = $blb->load($addr, true);
        $md5 = $blb->getMD5();
        $xls = $blb->getData();

        $multiBudget = new MultiBudget(array(GLYCONET_BUDGET_STRUCTURE2, GLYCONET_NI_BUDGET_STRUCTURE2), $xls);
        if($multiBudget->nBudgets() > 0){
            $this->html .= $multiBudget->render();
            $this->html .= "<p><a class='externalLink' href='{$wgServer}{$wgScriptPath}/index.php?action=downloadBlob&id={$md5}&mime=application/vnd.ms-excel&fileName={$this->project->getName()}_Budget.xlsx'>Download Budget</a></p>";
        }
    }
}    
    
?>
