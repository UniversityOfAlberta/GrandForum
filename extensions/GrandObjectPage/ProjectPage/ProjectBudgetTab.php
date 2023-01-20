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
        if($config->getValue('networkName') == "FES"){
            $multiBudget = new MultiBudget(array($structure, FES_EQUIPMENT_STRUCTURE, FES_EXTERNAL_STRUCTURE), $contents);
        }
        else {
            $multiBudget = new MultiBudget(array($structure, $niStructure), $contents);
        }
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
        global $config, $wgMessage;
        $me = Person::newFromWgUser();
        $error = null;
        if(isset($_FILES)){
            foreach($_FILES as $key => $file){
                if($key == "budget"){
                    foreach($file['tmp_name'] as $year => $tmp){
                        if($tmp != ""){
                            $contents = file_get_contents($tmp);
                            // Network specific Budget Validations
                            if($config->getValue('networkName') == "FES"){
                                $structure = @constant(strtoupper(preg_replace("/[^A-Za-z0-9 ]/", '', $config->getValue('networkName'))).'_BUDGET_STRUCTURE');
                                $multiBudget = new MultiBudget(array($structure, FES_EQUIPMENT_STRUCTURE, FES_EXTERNAL_STRUCTURE), $contents);
                                $budget = $multiBudget->getBudget(0);
                                $nYears = $budget->copy()->where(HEAD_ROW, array("Direct Costs"))->trimCols()->nCols() - 1;
                                for($i=0; $i < $nYears; $i++){
                                    $request  = $budget->copy()
                                                       ->where(HEAD1_ROW, array('Request From Future Energy Systems', 'Request From Future Energy System'))
                                                       ->select(HEAD_MONEY)
                                                       ->limitCols($i, 1);
                                    $other    = $budget->copy()
                                                       ->where(HEAD2_ROW, array('Other Federal Funding'))
                                                       ->select(HEAD_MONEY)
                                                       ->limitCols($i, 1);
                                    $external = $budget->copy()
                                                       ->where(HEAD2_ROW, array('External Funding (not Federal)'))
                                                       ->select(HEAD_MONEY)
                                                       ->limitCols($i, 1);
                                    $total    = $budget->copy()
                                                       ->where(HEAD1_ROW, array('Total Funding for the project'))
                                                       ->select(HEAD_MONEY)
                                                       ->limitCols($i, 1);
                                                       
                                    $requestVal  = floatval(str_replace("$", "", $request->toString()));
                                    $otherVal    = floatval(str_replace("$", "", $other->toString()));
                                    $externalVal = floatval(str_replace("$", "", $external->toString()));
                                    $totalVal    = floatval(str_replace("$", "", $total->toString()));

                                    if(($request->size() == 0 ||
                                        $other->size() == 0 ||
                                        $external->size() == 0 ||
                                        $total->size() == 0) ||
                                       ($requestVal + $otherVal + $externalVal) != $totalVal){
                                        $error = "The totals in the budget do not add up.  Make sure that you did not modify the spreadsheet formulas.";
                                    } 
                                }
                            }
                            if($error == null){
                                $blb = new ReportBlob(BLOB_EXCEL, $year, 0, $this->project->getId());
                                $addr = ReportBlob::create_address(RP_LEADER, LDR_BUDGET, LDR_BUD_ALLOC, 0);
                                $blb->store($contents, $addr);
                                $this->updateAllocations($year, $contents);
                            }
                        }
                    }
                }
                else if($key == "justification_upload"){
                    foreach($file['tmp_name'] as $year => $tmp){
                        $contents = file_get_contents($tmp);
                        if($contents != ""){
                            $blb = new ReportBlob(BLOB_RAW, $year, 0, $this->project->getId());
                            $addr = ReportBlob::create_address(RP_LEADER, LDR_BUDGET, "LDR_BUD_JUSTIFICATION_UPLOAD", 0);
                            $blb->store($contents, $addr);
                        }
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
        if($error != null){
            return $error;
        }
        redirect($this->project->getUrl()."?tab=budget");
    }
    
    function canEdit(){
        return (!$this->project->isFeatureFrozen(FREEZE_BUDGET) && $this->visibility['isLead']);
    }
    
    function canGeneratePDF(){
        return true;
    }
    
    function generatePDFBody(){
        $this->showBudget(true);
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath;
        $project = $this->project;
        $me = Person::newFromId($wgUser->getId());
        
        $this->showBudget(false);
        
        return $this->html;
    }
    
    function generateEditBody(){
        return $this->generateBody();
    }

    function showBudget($renderForPDF=false){
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
            if($config->getValue('networkName') == "AGE-WELL"){
                $endYear = date('Y', time() + (1 * 30 * 24 * 60 * 60)); // Roll-over budget in December
                $midYear = date('Y', time() - (3 * 30 * 24 * 60 * 60)); // Keep previous year open until April
            }
            else if($config->getValue('networkName') == "FES"){
                $endYear = date('Y'); // Roll-over budget in the new year
                $midYear = date('Y'); // Keep previous year open until the new year
            }
            else{
                $endYear = date('Y', time() - (3 * 30 * 24 * 60 * 60)); // Roll-over budget in April
                $midYear = date('Y', time() - (3 * 30 * 24 * 60 * 60)); // Keep previous year open until April
            }
            if($project->deleted){
                $startYear = substr($project->getDeleted(), 0, 4)-1;
            }
            
            $phaseDates = $config->getValue("projectPhaseDates");
            $startYear = max(substr($phaseDates[1], 0, 4), date('Y', strtotime($project->getCreated()) - (3 * 30 * 24 * 60 * 60)));
            
            for($i=$endYear; $i >= $startYear; $i--){
                $editable = ($i == $endYear || $i == $midYear || $me->isRoleAtLeast(STAFF));
                $this->html .= "<h3><a href='#'>".$i."/".substr($i+1,2,2)."</a></h3>";
                $this->html .= "<div style='overflow: auto;'>";
                
                // Last Year's Budget
                $blb = new ReportBlob(BLOB_EXCEL, $i-1, 0, $this->project->getId());
                $addr = ReportBlob::create_address(RP_LEADER, LDR_BUDGET, LDR_BUD_ALLOC, 0);
                $result = $blb->load($addr, true);
                $lastmd5 = $blb->getMD5();
                
                // Budget
                $blb = new ReportBlob(BLOB_EXCEL, $i, 0, $this->project->getId());
                $addr = ReportBlob::create_address(RP_LEADER, LDR_BUDGET, LDR_BUD_ALLOC, 0);
                $result = $blb->load($addr, true);
                $md5 = $blb->getMD5();
                $xls = $blb->getData();
                $structure = @constant(strtoupper(preg_replace("/[^A-Za-z0-9 ]/", '', $config->getValue('networkName'))).'_BUDGET_STRUCTURE');
                $niStructure = @constant(strtoupper(preg_replace("/[^A-Za-z0-9 ]/", '', $config->getValue('networkName'))).'_NI_BUDGET_STRUCTURE');
                if($config->getValue('networkName') == "AGE-WELL" && $i >= 2018){
                    // Account for change in structure
                    $niStructure = @constant(strtoupper(preg_replace("/[^A-Za-z0-9 ]/", '', $config->getValue('networkName'))).'_NI_BUDGET_STRUCTURE2');
                }
                if($config->getValue('networkName') == "AGE-WELL" && $i >= 2022){
                    $structure = @constant(strtoupper(preg_replace("/[^A-Za-z0-9 ]/", '', $config->getValue('networkName'))).'_BUDGET_STRUCTURE2022');
                    $niStructure = @constant(strtoupper(preg_replace("/[^A-Za-z0-9 ]/", '', $config->getValue('networkName'))).'_NI_BUDGET_STRUCTURE2022');
                }
                else if($config->getValue('networkName') == "AGE-WELL" && $i >= 2020){
                    $structure = @constant(strtoupper(preg_replace("/[^A-Za-z0-9 ]/", '', $config->getValue('networkName'))).'_BUDGET_STRUCTURE2020');
                    $niStructure = @constant(strtoupper(preg_replace("/[^A-Za-z0-9 ]/", '', $config->getValue('networkName'))).'_NI_BUDGET_STRUCTURE2020');
                }
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
                // Last Year's Justification Upload
                $blb = new ReportBlob(BLOB_RAW, $i-1, 0, $this->project->getId());
                $addr = ReportBlob::create_address(RP_LEADER, LDR_BUDGET, 'LDR_BUD_JUSTIFICATION_UPLOAD', 0);
                $result = $blb->load($addr);
                $lastjust_md5 = $blb->getMD5();
                // Justification Upload
                $blb = new ReportBlob(BLOB_RAW, $i, 0, $this->project->getId());
                $addr = ReportBlob::create_address(RP_LEADER, LDR_BUDGET, 'LDR_BUD_JUSTIFICATION_UPLOAD', 0);
                $result = $blb->load($addr);
                $just_md5 = $blb->getMD5();
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
                
                $allocationText = "Allocation Amount";
                if($config->getValue('networkName') == "AGE-WELL" && $i >= 2022){
                    $allocationText = "Remaining Project Funding Held by AGE-WELL";
                }
                
                if($edit && $editable){
                    if($config->getValue('networkName') != "AGE-WELL" || ($i != 2021)){
                        if($me->isRoleAtLeast(STAFF)){
                            $this->html .= "<h3 style='margin-top:0;padding-top:0;'>{$allocationText}</h3>
                                            $<input id='allocation$i' type='text' name='allocation[$i]' value='{$allocation}' /><br />
                                            <script type='text/javascript'>
                                                $('input#allocation$i').forceNumeric({min: 0, max: 100000000000,includeCommas: true});
                                            </script>";
                        }
                        else{
                            $this->html .= "<h3 style='margin-top:0;padding-top:0;'>{$allocationText}</h3>
                                            {$alloc}<br />";
                        }
                    }
                    if($config->getValue('networkName') != "AGE-WELL" || ($i != 2023 && $i != 2022 && $i != 2021)){
                        $this->html .= "<h3>Upload Budget</h3>
                                        <input type='file' name='budget[$i]' accept='.xls,.xlsx' /><br />";
                    }
                }
                
                if(!$edit || !$editable){
                    if($config->getValue('networkName') == "AGE-WELL" && $i == 2021){
                        // Show nothing
                    }
                    else{
                        $this->html .= "<h3 style='margin-top:0;padding-top:0;'>{$allocationText}</h3>
                                            $alloc<br /><br />";
                    }
                    if($config->getValue('networkName') == "FES"){
                        if(preg_match("/.*-T.*/", $this->project->getName())){
                            $multiBudget = new MultiBudget(array($structure, FES_EXTERNAL_STRUCTURE), $xls);
                        }
                        else{
                            $multiBudget = new MultiBudget(array($structure, FES_EQUIPMENT_STRUCTURE, FES_EXTERNAL_STRUCTURE), $xls);
                        }
                    }
                    else {
                        $multiBudget = new MultiBudget(array($structure, $niStructure), $xls);
                    }
                    if($multiBudget->nBudgets() > 0){
                        foreach($multiBudget->getBudgets() as $budget){
                            $budget->trim();
                        }
                        $budget = $multiBudget->getBudget(0);
                        $total = str_replace('$', '', $budget->copy()->select(COL_TOTAL)->where(COL_TOTAL)->toString());
                        if($total > $allocation && $allocation != ""){
                            if(!$config->getValue('networkName') == "AGE-WELL" || $i < 2021){
                                $budget->errors[0][] = "Your total '$".number_format($total)."' is greater than the allocated amount of '$".number_format($allocation)."'.";
                            }
                        }
                        if($renderForPDF){
                            $this->html .= "<div style='font-size:0.75em;'>{$multiBudget->renderForPDF()}</div>";
                        }
                        else{
                            $this->html .= $multiBudget->render();
                        }
                        $this->html .= "<a class='externalLink' href='{$wgServer}{$wgScriptPath}/index.php?action=downloadBlob&id={$md5}&mime=application/vnd.ms-excel&fileName={$project->getName()}_{$i}_Budget.xlsx'>Download Budget</a><br />";
                    }
                    else {
                        $this->html .= "No budget could be found for $i";
                    }
                    if($i > $startYear){
                        if($config->getValue('networkName') == "AGE-WELL"){
                            $justification = nl2br($justification);
                            if($just_md5 != ""){
                                $justification = "<a class='externalLink' href='{$wgServer}{$wgScriptPath}/index.php?action=downloadBlob&id={$just_md5}&mime=application/pdf&fileName={$project->getName()}_{$i}_Budget.pdf'>Download Justification</a><br />";
                            }
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
                        if($i == 2023){
                            // Special year
                            if($lastmd5 != "" || $lastjust_md5 != ""){
                                $this->html .= "<p>";
                                if($lastmd5 != ""){
                                    $this->html .= "<a class='externalLink' href='{$wgServer}{$wgScriptPath}/index.php?action=downloadBlob&id={$lastmd5}&mime=application/vnd.ms-excel&fileName={$project->getName()}_".($i-1)."_Budget.xlsx'>Last Year's Budget</a><br />";
                                }
                                if($lastjust_md5){
                                    $this->html .= "<a class='externalLink' href='{$wgServer}{$wgScriptPath}/index.php?action=downloadBlob&id={$lastjust_md5}&mime=application/pdf&fileName={$project->getName()}_".($i-1)."_Budget.pdf'>Download Justification</a><br />";
                                }
                                $this->html .= "</p>";
                            }
                            $this->html .= "<p>2023-24 is AGE-WELL’s final year as NCE and the final and fourth year of funding for all CRP/PPP projects. All project funds should be spent by Mar 31, 2024. The budget update process this year provides an opportunity to update the 2023-24 portion of your project budget that was submitted last year.</p>";
                            $this->html .= "<h3>Budget Update</h3>
                                            <p>Please provide an overview of any <u>major</u> changes that have been made to your project budget since it was last approved by the Research Management Committee (February 2022), or project developments that impact your project budget.</p>
                                            <textarea name='deviations[$i]' style='height:200px;resize: vertical;'>{$deviations}</textarea><br />";
                            $this->html .= "<p><b>Anticipated Unspent Project Funds as of March 31, 2023</b> $<input id='amount$i' type='text' name='carryoveramount[$i]' value='{$carryOverAmount}' /></p>";
                            $this->html .= "<p>Core Research Program (CRP) / Platform Projects (PPP): Projects may carry-forward 15% of 2022-23 funding to 2023-24 (calculated on the approved 2022-23 budget, including funds carried over from prior year). Funding over the 15% threshold will be deducted from new funding allocations in 2023 unless permission to carry forward the additional amount(s) is approved by the Research Management Committee.</p>
                                            <p>In the section below, provide a justification for the projected amount of unspent funds at year end and describe how funds will be spent by March 31, 2024. The amount of detail provided should be proportional to the amount of unspent funds. Please provide detail for each sub-project or investigator holding funds as part of your award.</p>
                                            <textarea name='carryover[$i]' style='height:200px;resize: vertical;'>{$carryOver}</textarea>";
                            $this->html .= "<h3>Upload Budget and Budget Justification</h3>
                                            <a href='{$wgServer}{$wgScriptPath}/data/AWCRP-PPP 2-year Budget.xlsx'>Budget Template</a><br />
                                            <p>Please upload a revised project budget for 2023-24 and in the following excel tabs, provide a budget breakdown for each Network Investigator who will be holding funds. The budget may include both funds carried forward from 2022-23 and new funding from AGE-WELL.</p>
                                            <p>In a separate free-form document, please provide an updated budget justification for expenditures with details in each category where a budget request has been made. Confirmed and projected partner contributions (cash and in-kind) are critical to include.</p>";
                            $this->html .= "<h4>Upload Revised Budget</h4>
                                            <input type='file' name='budget[$i]' accept='.xls,.xlsx' /><br />";
                            $this->html .= "<h4>Upload Revised Budget Justification</h4>
                                            <input type='file' name='justification_upload[$i]' accept='.pdf' /><br />";
                            $this->html .= "<script type='text/javascript'>
                                            $('input#amount$i').forceNumeric({min: 0, max: 100000000000,includeCommas: true});
                                        </script>";
                        }
                        else if($i == 2022){
                            // Special year
                            $this->html .= "<p>Project Leads are asked to submit two-year project budgets that reflect their planned expenses for 2022-23 and 2023-24.</p>
<p>As long as a project is approved by RMC to continue in 2022-23, investigators can expect approval to carry forward unspent funds with justification.</p>";
                            $this->html .= "<h3>Budget Update</h3>
                                            <p>Please provide an overview of any <u>major</u> changes that have been made to your project budget since it was last approved by the Research Management Committee (Feb 2021).</p>
                                            <textarea name='deviations[$i]' style='height:200px;resize: vertical;'>{$deviations}</textarea><br />";
                            $this->html .= "<p><b>Anticipated Unspent Project Funds as of March 31, 2022</b> $<input id='amount$i' type='text' name='carryoveramount[$i]' value='{$carryOverAmount}' /></p>";
                            $this->html .= "<p>Core Research Program (CRP) / Platform Projects (PPP): Project funds may carry forward funding to the next fiscal year provided a reasonable justification is provided and approved below.</p>
                                            <p>In the section below, provide a justification for the projected amount of unspent funds at year end describe how funds will be spent moving forward. Please provide detail for each sub-project or investigator holding funds as part of your award.</p>
                                            <textarea name='carryover[$i]' style='height:200px;resize: vertical;'>{$carryOver}</textarea>";
                            $this->html .= "<h3>Upload Budget and Budget Justification</h3>
                                            <a href='{$wgServer}{$wgScriptPath}/data/AWCRP-PPP 2-year Budget.xlsx'>Budget Template</a><br />
                                            <p>Please upload a two-year project budget and provide a budget breakdown for each Network Investigator that will be holding funds in the following excel tabs. The budget should be a best estimate of spending in fiscal years 2022-23 and 2023-24 and may include both funds carried forward from 2021 and new funding from AGE-WELL.</p>
                                            <p>In a separate free-form document, please provide a standard budget justification for expenditures with details in each category where a budget request has been made. Confirmed and projected partner contributions (cash and in-kind) are critical to include.</p>";
                            $this->html .= "<h4>Upload Budget</h4>
                                            <input type='file' name='budget[$i]' accept='.xls,.xlsx' /><br />";
                            $this->html .= "<h4>Budget Justification</h4>
                                            <input type='file' name='justification_upload[$i]' accept='.pdf' /><br />";
                            $this->html .= "<script type='text/javascript'>
                                            $('input#amount$i').forceNumeric({min: 0, max: 100000000000,includeCommas: true});
                                        </script>";
                        }
                        else if($i == 2021){
                            // Special year
                            $this->html .= "<p>Project Leads are asked to submit budgets that reflect their best estimate of planned expenses for 2021-22. We anticipate that budgets may include a mix of funds carried forward from 2020-21 and new funding from AGE-WELL in 2021-22.</p>
<p>As long as a project will advance in 2021, investigators can expect approval to carry forward unspent funds. The amount of new funding issued for April 1 will take into account your carry forward request, applying a formula so that you will have the budget you need even if you over or underestimate the amount of unspent funds at the time of this update.
Please check your original Notice of Award for the approved total grant value.</p>";
                            $this->html .= "<h3>Budget Update</h3>
                                            <p>Please provide an overview of any <u>major</u> changes that have been made to your project budget since it was last approved by the Research Management Committee (Jan 2020).</p>
                                            <textarea name='deviations[$i]' style='height:200px;resize: vertical;'>{$deviations}</textarea><br />";
                            $this->html .= "<p><b>Anticipated Unspent Project Funds as of March 31, 2021:</b> $<input id='amount$i' type='text' name='carryoveramount[$i]' value='{$carryOverAmount}' /></p>";
                            $this->html .= "<p>Core Research Program (CRP) / Platform Projects (PPP): Project funds may carry forward to the next fiscal year provided a project plan update was submitted in January 2021 and a reasonable justification for unspent funds is included as part of this update.</p>
<p>Note that any NSERC COVID Supplement funds associated with your AGE-WELL grant cannot be carried forward. Unspent funds listed here should reflect the initial AGE-WELL award only. 
Please provide detail for each sub-project or investigator holding funds as part of your award.</p>
                                            <textarea name='carryover[$i]' style='height:200px;resize: vertical;'>{$carryOver}</textarea>";
                            $this->html .= "<h3>Upload Budget and Budget Justification</h3>
                                            <a href='{$wgServer}{$wgScriptPath}/data/AGE-WELL Budget2021-22.xlsx'>Budget Template</a><br />
                                            <p>Please upload a 2021-22 project budget and provide a budget breakdown for each Network Investigator that will be holding funds in 2021-22 in the following excel tabs.  The budget should be a best estimate of spending in the year ahead and may include both funds carried forward from 2020 and new funding from AGE-WELL.</p>
                                            <p>In a separate free-form document, please provide a standard budget justification with details in each category where a budget request has been made. Confirmed and projected partner contributions (cash and in-kind) are critical to include for the upcoming year.</p>";
                            $this->html .= "<h4>Upload Budget</h4>
                                            <input type='file' name='budget[$i]' accept='.xls,.xlsx' /><br />";
                            $this->html .= "<h4>Budget Justification</h4>
                                            <input type='file' name='justification_upload[$i]' accept='.pdf' /><br />";
                            $this->html .= "<script type='text/javascript'>
                                            $('input#amount$i').forceNumeric({min: 0, max: 100000000000,includeCommas: true});
                                        </script>";
                        }
                        else {
                            // Normal year
                            $this->html .= "<p>Please upload your $i/".substr(($i+1),2,2)." project budget and provide a budget breakdown on the following excel tabs for each Network Investigator that will be holding funds in Year ".$i."-".($i+1).".</p>";
                            if($i >= 2020){
                                $this->html .= "<a href='{$wgServer}{$wgScriptPath}/data/AGE-WELL Budget2020.xlsx'>Budget Template</a>";
                            }
                            else{
                                $this->html .= "<a href='{$wgServer}{$wgScriptPath}/data/AGE-WELL Budget.xlsx'>Budget Template</a>";
                            }
                            $this->html .= "<h3>Budget Justification</h3>";
                            $this->html .= "<p>Please provide a detailed justification for each category where a budget request has been made. Justifications should include the rationale for the requested item, such as the need for the specified number of HQP or the requested budget, as well as details on any partner contributions that you may be receiving. Confirmed and projected partner contributions (cash and in-kind) are critical to include for the upcoming year.</p>
                                            <p>Note: Unless changes have been made, this information can be copied and pasted from the budget request submitted with your approved application.</p>";
                            $this->html .= "<textarea name='justification[$i]' style='height:200px;resize: vertical;'>{$justification}</textarea>
                                            <h3>Budget Update</h3>";
                            $this->html .= "<p>If relevant, please provide a description of any changes that have been made to your $i/".substr(($i+1),2,2)." budget since it was last approved by the Research Management Committee.</p>";
                            $this->html .= "<textarea name='deviations[$i]' style='height:200px;resize: vertical;'>{$deviations}</textarea><br />";
                            $this->html .= "<p><b>Anticipated Unspent Project Funds as of March 31, ".($i+1).":</b> $<input id='amount$i' type='text' name='carryoveramount[$i]' value='{$carryOverAmount}' /></p>";
                            $this->html .= "<p>Core Research Program (CRP): As stated in the funding letter, the permissible carry forward of funds for fiscal year 2020/21 is 15%. If greater than 15% of total project funds are unspent, approval to carry forward funds via a detailed justification to the Research Management Committee is required.</p>";
                            
                            $this->html .= "<p>Innovation Hubs and Platform Projects: As stated in the extension letter, the permissible carry forward of funds for fiscal year 2020/21 is 15%. If greater than 15% of project funds are unspent, approval to carry forward funds via a detailed justification to the Research Management Committee is required.</p>";
                            
                            $this->html .= "<p>Workpackages (WP)/Cross-Cutting (CC) Activities: As stated in the WP stipend and CC extension letters, no funds can be carried forward. All unspent funds will be recalled by AGE-WELL once the Network Management Office has received the Form 300s from your respective institutions.</p>";
                            
                            $this->html .= "<p>Please provide a justification for the projected amount of unspent funds at year end and use this space to justify carrying forward amounts over 15%. Please also describe how these funds will be spent in 2021/22 once approved.</p>";
                            $this->html .= "<textarea name='carryover[$i]' style='height:200px;resize: vertical;'>{$carryOver}</textarea>
                                        <script type='text/javascript'>
                                            $('input#amount$i').forceNumeric({min: 0, max: 100000000000,includeCommas: true});
                                        </script>";
                        }
                    }
                }
                if($edit && $config->getValue('networkName') == "FES" && $editable){
                    if(preg_match("/.*-T.*/", $this->project->getName())){
                        $template = "FES_Project_Budget_Tsinghua.xlsx";
                    }
                    else {
                        $template = "FES_Project_Budget.xlsx";
                    }
                    $this->html .= "<a href='{$wgServer}{$wgScriptPath}/data/{$template}'>Budget Template</a>";
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
