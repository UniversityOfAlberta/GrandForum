<?php

class ProjectBudgetTab extends AbstractEditableTab {

    var $project;
    var $visibility;

    function ProjectBudgetTab($project, $visibility){
        parent::AbstractTab("Budget");
        $this->project = $project;
        $this->visibility = $visibility;
    }
    
    function addAllocation($year, $amount, $ni){
        DBFunctions::insert("grand_allocations",
                            array('user_id' => $ni->getId(),
                                  'project_id' => $this->project->getId(),
                                  'year' => $year,
                                  'amount' => $amount));
        DBFunctions::commit();
    }
    
    function updateAllocations($year, $contents){
        global $config;
        $structure = @constant(strtoupper(preg_replace("/[^A-Za-z0-9 ]/", '', $config->getValue('networkName'))).'_BUDGET_STRUCTURE');
        $niStructure = @constant(strtoupper(preg_replace("/[^A-Za-z0-9 ]/", '', $config->getValue('networkName'))).'_NI_BUDGET_STRUCTURE');
        
        $multiBudget = new MultiBudget(array($structure, $niStructure), $contents);
        if($config->getValue('networkName') == "AGE-WELL"){
            // AGE-WELL Budget Allocations
            DBFunctions::delete("grand_allocations",
                                array('project_id' => EQ($this->project->getId()),
                                      'year' => EQ($year)));
            foreach($multiBudget->getBudgets() as $budget){
                $niBudget = $budget->copy()->select(V_PERS_NOT_NULL)->where(V_PERS_NOT_NULL);
                $totalBudget = $budget->copy()->select(COL_TOTAL)->where(COL_TOTAL);
                if($niBudget->size() == 1 && $totalBudget->size() == 1){
                    $name = $niBudget->toString();
                    $total = str_replace('$', '', $totalBudget->toString());
                    
                    $valid = true;
                    $ni = Person::newFromNameLike($name);
                    if($ni == null || $ni->getName() == ""){
                        $ni = Person::newFromReversedName($name);
                    }
                    if($ni == null || $ni->getName() == ""){
                        $valid = false;
                    }
                    var_dump($total);
                    if($valid && $total != "" && $total != 0){
                        $this->addAllocation($year, $total, $ni);
                    }
                }
            }
        }
    }
    
    function handleEdit(){
        $me = Person::newFromWgUser();
        if(isset($_FILES)){
            foreach($_FILES as $key => $file){
                foreach($file['tmp_name'] as $year => $tmp){
                    if($tmp != ""){
                        $contents = file_get_contents($tmp);
                        
                        $blb = new ReportBlob(BLOB_EXCEL, $year, 0, $this->project->getId());
                        $addr = ReportBlob::create_address(RP_LEADER, LDR_BUDGET, LDR_BUD_ALLOC, 0);
                        $blb->store($contents, $addr);
                        $this->updateAllocations($year, $contents);
                    }
                }
            }
        }
        if($me->isRoleAtLeast(STAFF)){
            if(isset($_POST['allocation'])){
                foreach($_POST['allocation'] as $year => $allocation){
                    $allocation = str_replace(">", "&gt;",
                                  str_replace("'", "&#39;",
                                  str_replace("<", "&lt;", $allocation)));
                    $allocation = str_replace(",", "", $allocation);
                    $blb = new ReportBlob(BLOB_TEXT, $year, 0, $this->project->getId());
                    $addr = ReportBlob::create_address(RP_LEADER, LDR_BUDGET, 'LDR_BUD_ALLOCATION', 0);
                    $blb->store($allocation, $addr);
                }
            }
        }
        if(isset($_POST['justification'])){
            foreach($_POST['justification'] as $year => $justification){
                $justification = str_replace(">", "&gt;", 
                              str_replace("<", "&lt;", $justification));
                $blb = new ReportBlob(BLOB_TEXT, $year, 0, $this->project->getId());
                $addr = ReportBlob::create_address(RP_LEADER, LDR_BUDGET, 'LDR_BUD_JUSTIFICATION', 0);
                $blb->store($justification, $addr);
            }
        }
        if(isset($_POST['deviations'])){
            foreach($_POST['deviations'] as $year => $deviations){
                $deviations = str_replace(">", "&gt;", 
                              str_replace("<", "&lt;", $deviations));
                $blb = new ReportBlob(BLOB_TEXT, $year, 0, $this->project->getId());
                $addr = ReportBlob::create_address(RP_LEADER, LDR_BUDGET, 'LDR_BUD_DEVIATIONS', 0);
                $blb->store($deviations, $addr);
            }
        }
        if(isset($_POST['carryoveramount'])){
            foreach($_POST['carryoveramount'] as $year => $carryOver){
                $carryOver = str_replace(">", "&gt;",
                             str_replace("'", "&#39;",
                             str_replace("<", "&lt;", $carryOver)));
                $carryOver = str_replace(",", "", $carryOver);
                $blb = new ReportBlob(BLOB_TEXT, $year, 0, $this->project->getId());
                $addr = ReportBlob::create_address(RP_LEADER, LDR_BUDGET, 'LDR_BUD_CARRYOVERAMOUNT', 0);
                $blb->store($carryOver, $addr);
            }
        }
        if(isset($_POST['carryover'])){
            foreach($_POST['carryover'] as $year => $carryOver){
                $carryOver = str_replace(">", "&gt;", 
                             str_replace("<", "&lt;", $carryOver));
                $blb = new ReportBlob(BLOB_TEXT, $year, 0, $this->project->getId());
                $addr = ReportBlob::create_address(RP_LEADER, LDR_BUDGET, 'LDR_BUD_CARRYOVER', 0);
                $blb->store($carryOver, $addr);
            }
        }
        redirect($this->project->getUrl()."?tab=budget");
    }
    
    function canEdit(){
        return (!$this->project->isFeatureFrozen(FREEZE_DESCRIPTION) && $this->visibility['isLead']);
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath;
        $project = $this->project;
        $me = Person::newFromId($wgUser->getId());
        
        $this->showBudget();
        
        return $this->html;
    }
    
    function generateEditBody(){
        return $this->generateBody();
    }

    function showBudget(){
        global $wgServer, $wgScriptPath, $wgUser, $wgOut, $config;
        $me = Person::newFromWgUser();
        $edit = (isset($_POST['edit']) && $this->canEdit() && !isset($this->visibility['overrideEdit']));
        $project = $this->project;
        
        if($me->isMemberOf($this->project) || $this->visibility['isLead']){
            $wgOut->addScript("<script type='text/javascript'>
                $(document).ready(function(){
                    $('#budgetAccordion').accordion({autoHeight: false,
                                                     collapsible: true});
                });
            </script>");
            $this->html .= "<div id='budgetAccordion'>";
            $endYear = date('Y', time() - (9 * 30 * 24 * 60 * 60));
            if($project->deleted){
                $startYear = substr($project->getDeleted(), 0, 4)-1;
            }
            $phaseDates = $config->getValue("projectPhaseDates");
            $startYear = max(substr($phaseDates[1], 0, 4), substr($project->getCreated(), 0, 4));
            
            for($i=$endYear+1; $i >= $startYear; $i--){
                $this->html .= "<h3><a href='#'>".$i."/".substr($i+1,2,2)."</a></h3>";
                $this->html .= "<div style='overflow: auto;'>";
                // Budget
                $blb = new ReportBlob(BLOB_EXCEL, $i, 0, $this->project->getId());
                $addr = ReportBlob::create_address(RP_LEADER, LDR_BUDGET, LDR_BUD_ALLOC, 0);
                $result = $blb->load($addr, true);
                $md5 = $blb->getMD5();
                $xls = $blb->getData();
                $structure = @constant(strtoupper(preg_replace("/[^A-Za-z0-9 ]/", '', $config->getValue('networkName'))).'_BUDGET_STRUCTURE');
                $niStructure = @constant(strtoupper(preg_replace("/[^A-Za-z0-9 ]/", '', $config->getValue('networkName'))).'_NI_BUDGET_STRUCTURE');
                // Allocation
                $blb = new ReportBlob(BLOB_TEXT, $i, 0, $this->project->getId());
                $addr = ReportBlob::create_address(RP_LEADER, LDR_BUDGET, 'LDR_BUD_ALLOCATION', 0);
                $result = $blb->load($addr);
                $allocation = $blb->getData();
                // Justification
                $blb = new ReportBlob(BLOB_TEXT, $i, 0, $this->project->getId());
                $addr = ReportBlob::create_address(RP_LEADER, LDR_BUDGET, 'LDR_BUD_JUSTIFICATION', 0);
                $result = $blb->load($addr);
                $justification = $blb->getData();
                // Deviations
                $blb = new ReportBlob(BLOB_TEXT, $i, 0, $this->project->getId());
                $addr = ReportBlob::create_address(RP_LEADER, LDR_BUDGET, 'LDR_BUD_DEVIATIONS', 0);
                $result = $blb->load($addr);
                $deviations = $blb->getData();
                // Carry Forward Amount
                $blb = new ReportBlob(BLOB_TEXT, $i, 0, $this->project->getId());
                $addr = ReportBlob::create_address(RP_LEADER, LDR_BUDGET, 'LDR_BUD_CARRYOVERAMOUNT', 0);
                $result = $blb->load($addr);
                $carryOverAmount = ($blb->getData() != "") ? $blb->getData() : 0;
                // Carry Forward
                $blb = new ReportBlob(BLOB_TEXT, $i, 0, $this->project->getId());
                $addr = ReportBlob::create_address(RP_LEADER, LDR_BUDGET, 'LDR_BUD_CARRYOVER', 0);
                $result = $blb->load($addr);
                $carryOver = $blb->getData();
                
                if($allocation == ""){
                    $alloc = "TBA";
                }
                else {
                    $alloc = "\$".number_format($allocation);
                }
                
                if($edit){
                    if($me->isRoleAtLeast(STAFF)){
                        $this->html .= "<h3 style='margin-top:0;padding-top:0;'>Allocation Amount</h3>
                                        $<input id='allocation$i' type='text' name='allocation[$i]' value='{$allocation}' /><br />
                                        <script type='text/javascript'>
                                            $('input#allocation$i').forceNumeric({min: 0, max: 100000000000,includeCommas: true});
                                        </script>";
                    }
                    else{
                        $this->html .= "<h3 style='margin-top:0;padding-top:0;'>Allocation Amount</h3>
                                        {$alloc}<br />";
                    }
                    $this->html .= "<h3>Upload Budget</h3>
                                    <input type='file' name='budget[$i]' accept='.xls,.xlsx' /><br />";
                }
                
                if(!$edit){
                    $this->html .= "<h3 style='margin-top:0;padding-top:0;'>Allocation Amount</h3>
                                        $alloc<br /><br />";
                    $multiBudget = new MultiBudget(array($structure, $niStructure), $xls);
                    if($multiBudget->nBudgets() > 0){
                        $budget = $multiBudget->getBudget(0);
                        $total = str_replace('$', '', $budget->copy()->select(COL_TOTAL)->where(COL_TOTAL)->toString());
                        if($total > $allocation && $allocation != ""){
                            $budget->errors[0][] = "Your total '$".number_format($total)."' is greater than the allocated amount of '$".number_format($allocation)."'.";
                        }
                        $this->html .= $multiBudget->render();
                        $this->html .= "<a href='{$wgServer}{$wgScriptPath}/index.php?action=downloadBlob&id={$md5}&mime=application/vnd.ms-excel&fileName={$project->getName()}_{$i}_Budget.xlsx'>Download Budget</a><br />";
                    }
                    else {
                        $this->html .= "No budget could be found for $i";
                    }
                    if($i > $startYear){
                        if($config->getValue('networkName') == "AGE-WELL"){
                            $justification = nl2br($justification);
                            $deviations = nl2br($deviations);
                            $carryOver = nl2br($carryOver);
                            $this->html .= "<h3>Budget Justification</h3>
                                            {$justification}
                                            <h3>Budget Update</h3>
                                            {$deviations}
                                            <h3>Carry Forward</h3>
                                            <p><b>Amount:</b> \$".number_format($carryOverAmount)."</p>
                                            {$carryOver}";
                        }
                    }
                    if($config->getValue('networkName') == "FES"){
                        $justification = nl2br($justification);
                        $this->html .= "<h3>Budget Justification</h3>
                                        {$justification}";
                    }
                }
                else if($i > $startYear){
                    if($config->getValue('networkName') == "AGE-WELL"){
                        $this->html .= "<a href='{$wgServer}{$wgScriptPath}/data/AGE-WELL Budget.xlsx'>Budget Template</a>";
                        $this->html .= "<h3>Budget Justification</h3>
                                        <p>Please provide a detailed justification for each category where a budget request has been made.  Justifications should include the rationale for the requested item";
                        if(strpos($project->getName(), "CC") !== 0){
                            $this->html .= ", such as the need for the specified number of HQP or the requested budget, as well as details on any partner contributions that you may be receiving";
                        }
                        $this->html .= ".</p>
                                        <textarea name='justification[$i]' style='height:200px;resize: vertical;'>{$justification}</textarea>
                                        <h3>Budget Update</h3>
                                        <p>Please describe any proposed changes to your Year ".($i-$startYear+1)." ($i/".substr(($i+1),2,2).") ";
                        if(strpos($project->getName(), "CC") !== 0){
                            $this->html .= "budget from what was anticipated at the start of your project (e.g. changes to co-investigators, HQP or other significant adjustments).</p>";
                        }
                        else{
                            $this->html .= "plans if your activities represent a significant shift in direction from previous years’ work.";
                        }
                        $this->html .= "<textarea name='deviations[$i]' style='height:200px;resize: vertical;'>{$deviations}</textarea>
                                        <h3>Carry Forward</h3>";
                        if(strpos($project->getName(), "CC") !== 0){
                            $this->html .= "<p>Network Investigators are only eligible to carry forward 15% of the funds received. Only under exceptional circumstances will a greater than 15% carry forward be approved by the Research Management Committee (RMC). Unless the AGE-WELL RMC has granted approval, any amount above the carry forward maximum will be recalled.</p><br />";
                        }
                        else{
                            $this->html .= "<p>Please list the anticipated amount of unspent funds held by this CC (project total and location where funds are held) as of March 31.</p><br />";
                        }
                        $this->html .= "<p>Total amount of the project budget you wish to carry forward to Year ".($i-$startYear+1).": $<input id='amount$i' type='text' name='carryoveramount[$i]' value='{$carryOverAmount}' /></p><br />
                                        <p>If carry forward is requested, please provide a justification of the amount per investigator that you request to transfer to Year ".($i-$startYear+1);
                        if(strpos($project->getName(), "CC") !== 0){
                            $this->html .= ", including any amounts greater than fifteen percent (15%)";
                        }
                        $this->html .=".  Please also include a justification for how these funds will be spent in $i/".($i+1);
                        if(strpos($project->getName(), "CC") !== 0){
                            $this->html .= " once approved";
                        }
                        $this->html .= ".</p>
                                        <textarea name='carryover[$i]' style='height:200px;resize: vertical;'>{$carryOver}</textarea>
                                        <script type='text/javascript'>
                                            $('input#amount$i').forceNumeric({min: 0, max: 100000000000,includeCommas: true});
                                        </script>";
                    }
                }
                if($edit && $config->getValue('networkName') == "FES"){
                    $this->html .= "<a href='{$wgServer}{$wgScriptPath}/data/FES_Project_Budget.xlsx'>Budget Template</a>";
                    $this->html .= "<h3>Budget Justification</h3>
                                    <textarea name='justification[$i]' style='height:200px;resize: vertical;'>{$justification}</textarea>";
                }
                $this->html .="</div>";
            }
            if($i == $startYear){
                $this->html .= "No Allocated Budgets have been created yet for this project.";
            }
            $this->html .= "</div>";

        }
    }
}    
    
?>
