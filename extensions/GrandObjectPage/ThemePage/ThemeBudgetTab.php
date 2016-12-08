<?php

class ThemeBudgetTab extends AbstractEditableTab {

    var $theme;
    var $visibility;

    function ThemeBudgetTab($theme, $visibility){
        parent::AbstractTab("Budget");
        $this->theme = $theme;
        $this->visibility = $visibility;
    }
    
    function handleEdit(){
        $me = Person::newFromWgUser();
        if(isset($_FILES)){
            foreach($_FILES as $key => $file){
                foreach($file['tmp_name'] as $year => $tmp){
                    if($tmp != ""){
                        $contents = file_get_contents($tmp);
                        
                        $blb = new ReportBlob(BLOB_EXCEL, $year, 0, $this->theme->getId());
                        $addr = ReportBlob::create_address('RP_THEME', 'THEME_BUDGET', 'THEME_BUD_ALLOC', 0);
                        $blb->store($contents, $addr);
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
                    $blb = new ReportBlob(BLOB_TEXT, $year, 0, $this->theme->getId());
                    $addr = ReportBlob::create_address('RP_THEME', 'THEME_BUDGET', 'THEME_BUD_ALLOCATION', 0);
                    $blb->store($allocation, $addr);
                }
            }
        }
        if(isset($_POST['justification'])){
            foreach($_POST['justification'] as $year => $justification){
                $justification = str_replace(">", "&gt;", 
                              str_replace("<", "&lt;", $justification));
                $blb = new ReportBlob(BLOB_TEXT, $year, 0, $this->theme->getId());
                $addr = ReportBlob::create_address('RP_THEME', 'THEME_BUDGET', 'THEME_BUD_JUSTIFICATION', 0);
                $blb->store($justification, $addr);
            }
        }
        if(isset($_POST['carryoveramount'])){
            foreach($_POST['carryoveramount'] as $year => $carryOver){
                $carryOver = str_replace(">", "&gt;",
                             str_replace("'", "&#39;",
                             str_replace("<", "&lt;", $carryOver)));
                $carryOver = str_replace(",", "", $carryOver);
                $blb = new ReportBlob(BLOB_TEXT, $year, 0, $this->theme->getId());
                $addr = ReportBlob::create_address('RP_THEME', 'THEME_BUDGET', 'THEME_BUD_CARRYOVERAMOUNT', 0);
                $blb->store($carryOver, $addr);
            }
        }
        redirect($this->theme->getUrl()."?tab=budget");
    }
    
    function canEdit(){
        return $this->visibility['isLead'];
    }
    
    function generateBody(){
        global $wgUser, $wgServer, $wgScriptPath;
        $theme = $this->theme;
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
        $isLead = $this->visibility['isLead'];
        $edit = (isset($_POST['edit']) && $this->canEdit() && !isset($this->visibility['overrideEdit']));
        $theme = $this->theme;
        
        if($isLead){
            $wgOut->addScript("<script type='text/javascript'>
                $(document).ready(function(){
                    $('#budgetAccordion').accordion({autoHeight: false,
                                                     collapsible: true});
                });
            </script>");
            $this->html .= "<div id='budgetAccordion'>";
            $endYear = date('Y', time() - (9 * 30 * 24 * 60 * 60));

            $phaseDates = $config->getValue("projectPhaseDates");
            $startYear = substr($phaseDates[$theme->getPhase()], 0, 4);
            
            for($i=$endYear+1; $i >= $startYear; $i--){
                $this->html .= "<h3><a href='#'>".$i."/".substr($i+1,2,2)."</a></h3>";
                $this->html .= "<div style='overflow: auto;'>";
                // Budget
                $blb = new ReportBlob(BLOB_EXCEL, $i, 0, $this->theme->getId());
                $addr = ReportBlob::create_address('RP_THEME', 'THEME_BUDGET', 'THEME_BUD_ALLOC', 0);
                $result = $blb->load($addr, true);
                $md5 = $blb->getMD5();
                $xls = $blb->getData();
                $structure = @constant(strtoupper(preg_replace("/[^A-Za-z0-9 ]/", '', $config->getValue('networkName'))).'_BUDGET_STRUCTURE');
                $niStructure = @constant(strtoupper(preg_replace("/[^A-Za-z0-9 ]/", '', $config->getValue('networkName'))).'_NI_BUDGET_STRUCTURE');
                // Allocation
                $blb = new ReportBlob(BLOB_TEXT, $i, 0, $this->theme->getId());
                $addr = ReportBlob::create_address('RP_THEME', 'THEME_BUDGET', 'THEME_BUD_ALLOCATION', 0);
                $result = $blb->load($addr);
                $allocation = $blb->getData();
                // Justification
                $blb = new ReportBlob(BLOB_TEXT, $i, 0, $this->theme->getId());
                $addr = ReportBlob::create_address('RP_THEME', 'THEME_BUDGET', 'THEME_BUD_JUSTIFICATION', 0);
                $result = $blb->load($addr);
                $justification = $blb->getData();
                // Carry Forward Amount
                $blb = new ReportBlob(BLOB_TEXT, $i, 0, $this->theme->getId());
                $addr = ReportBlob::create_address('RP_THEME', 'THEME_BUDGET', 'THEME_BUD_CARRYOVERAMOUNT', 0);
                $result = $blb->load($addr);
                $carryOverAmount = ($blb->getData() != "") ? $blb->getData() : 0;
                
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
                        $this->html .= "<a href='{$wgServer}{$wgScriptPath}/index.php?action=downloadBlob&id={$md5}&mime=application/vnd.ms-excel&fileName={$theme->getAcronym()}_{$i}_Budget.xlsx'>Download Budget</a><br />";
                    }
                    else {
                        $this->html .= "No budget could be found for $i";
                    }
                    if($i > $startYear){
                        if($config->getValue('networkName') == "AGE-WELL"){
                            $justification = nl2br($justification);
                            $this->html .= "<h3>Budget Justification</h3>
                                            {$justification}
                                            <h3>Carry Forward</h3>
                                            <p><b>Amount:</b> \$".number_format($carryOverAmount)."</p>";
                        }
                    }
                }
                else if($i > $startYear){
                    if($config->getValue('networkName') == "AGE-WELL"){
                        $this->html .= "<a href='{$wgServer}{$wgScriptPath}/data/AGE-WELL WP Budget.xlsx'>Budget Template</a>";
                        $this->html .= "<h3>Budget Justification</h3>
                                        <p>Please provide a detailed justification for each category where a budget request has been made. Justifications should include the rationale for the requested item.</p>
                                        <p>It is requested that all Y".(($i-$startYear)+1)." funds are distributed at this time. It is understood that WPLs may not know where/how all of the funds will be spent throughout the year. It is asked that the funds are handled by the WPLs throughout the year via invoicing/expense reimbursement, and avoid second order transfers, as funds are spent throughout the year.</p>
                                        <textarea name='justification[$i]' style='height:200px;resize: vertical;'>{$justification}</textarea>
                                        <h3>Carry Forward</h3>
                                        <p>Total amount of the unspent Year ".($i-$startYear)." {$config->getValue('projectThemes')} budget funds: $<input id='amount$i' type='text' name='carryoveramount[$i]' value='{$carryOverAmount}' /></p>
                                        <p><small>*Note: the total amount of unspent funds will be deducted from your Year ".($i-$startYear+1)." budget. Please ensure that the reduction of Y".($i-$startYear)." unspent funds from your Year ".($i-$startYear+1)." budget is factored in above.</small></p>
                                        <script type='text/javascript'>
                                            $('input#amount$i').forceNumeric({min: 0, max: 100000000000,includeCommas: true});
                                        </script>";
                    }
                }
                $this->html .="</div>";
            }
            if($i == $startYear){
                $this->html .= "No Allocated Budgets have been created yet for this {$config->getValue('projectThemes')}.";
            }
            $this->html .= "</div>";

        }
    }
}    
    
?>
