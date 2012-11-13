<?php

class NIProgressReportItem extends StaticReportItem {

	function render(){
	    global $wgOut;
        $details = $this->getTableHTML();
        $item = "$details";
        $item = $this->processCData($item);
		$wgOut->addHTML($item);
	}
	
	function renderForPDF(){
	    global $wgOut;
        $details = $this->getTableHTML();
        $item = "$details";
        $item = $this->processCData($item);
		$wgOut->addHTML($item);
	}
	
	function getTableHTML(){
	    $person = Person::newFromId($this->personId);
        $project = $this->getReport()->project;
        
	    $reportType = $this->getAttr('reportType', 'NIReport');
        $report = new DummyReport($reportType, $person, $project);
        if($project != null){
            $projects = array($project);
        }
        else{
            $projects = $person->getProjects();
        }
		
		$nMilestones = 0;
		$nComplete = 0;
		$nInvolved = 0;
		$nNotInvolved = 0;
		$nNotMentioned = 0;
		$nProjects = count($projects);
		$data = array();
		
        foreach($projects as $proj){
            $results = $this->findMilestones($proj, $report);
            $nComplete += $results['nComplete'];
            $nMilestones += $results['nMilestones'];
            $nInvolved += $results['nInvolved'];
            $nNotInvolved += $results['nNotInvolved'];
            $nNotMentioned += $results['nNotMentioned'];
            $data[$proj->getName()] = $results;
        }
        
        $errorMsg = "";
        $rowspan = 0;
        if($reportType == "NIReport"){
            $allocatedBudget = null;
            $wasNILastYear = ($person->isRoleDuring(CNI, (REPORTING_YEAR-1).REPORTING_CYCLE_START_MONTH, (REPORTING_YEAR-1).REPORTING_CYCLE_END_MONTH) || 
                              $person->isRoleDuring(PNI, (REPORTING_YEAR-1).REPORTING_CYCLE_START_MONTH, (REPORTING_YEAR-1).REPORTING_CYCLE_END_MONTH));
            if($wasNILastYear){
                $allocatedBudget = $person->getAllocatedBudget(REPORTING_YEAR-1);
            }
            $budget = null;
            $rep_addr = ReportBlob::create_address(RP_RESEARCHER, RES_BUDGET, 0, 0);
            $budget_blob = new ReportBlob(BLOB_EXCEL, REPORTING_YEAR, $person->getId(), 0);
            $budget_blob->load($rep_addr);
            $budgetData = $budget_blob->getData();
            if($budgetData != null){
                $budget = new Budget("XLS", REPORT2_STRUCTURE, $budgetData);
                $budget->filterCols(V_PROJ, array(""));
                if($person->isRoleDuring(CNI) && !$person->isRole(PNI)){
                    $errors = BudgetReportItem::addWorksWithRelation($budgetData, true);
                    foreach($errors as $key => $error){
	                    $budget->errors[0][] = $error;
	                }
                }
            }
            if($allocatedBudget == null && $wasNILastYear){
                $rowspan++;
            }
            if($budget == null || $budget->isError()){
                $rowspan++;
            }
            
            if($rowspan > 0){
                $errorMsg .= "<tr><td rowspan='$rowspan'><b>Budget</b></td>";
            }
            if($allocatedBudget == null && $wasNILastYear){
                $rowspan++;
                $errorMsg .= "<td><span class='inlineError'>You have not uploaded a revised budget for your ".REPORTING_YEAR." allocated funds</span></td></tr>\n";
            }
            
            $tr = "";
            if($allocatedBudget == null && $wasNILastYear){
                $tr = "<tr>";
            }
            if($budget == null){
                $errorMsg .= "$tr<td><span class='inlineError'>You have not uploaded a budget request</span></td></tr>\n";
            }
            else{
		        if($budget->isError()){
                    $errorMsg .= "$tr<td><span class='inlineError'>There are errors in your budget request</span></td></tr>\n";
                }
            }
        }
        
        $details = "";
        $rowspan = count($projects) + 1;
        if($rowspan > 1){
            $details .= "<tr><td valign='top' rowspan='1'><b>Milestones</b></td><td style='padding:0;'>";
            $details .= "<table cellpadding='1' frame='void' rules='all' width='100%'><tr><td><b>Projects</b></td><td><b>Working On</b></td><td><b>Comments On</b></td><td><b>NOT Involved</b></td><td><b>No Indication</b>\n</td></tr>";
            foreach($projects as $proj){
                $results = $data[$proj->getName()];
                $notMentionedErrorStart = "";
                $notMentionedErrorEnd = "";
                if($results['nNotMentioned'] > 0){
                    $notMentionedErrorStart = "<span class='inlineError'>";
                    $notMentionedErrorEnd = "</span>";
                }
                $details .= "<tr><td>{$proj->getName()}</td><td>{$results['nInvolved']} of {$results['nMilestones']}</td><td>{$results['nCommented']} of {$results['nMilestones']}</td><td>{$results['nNotInvolved']} of {$results['nMilestones']}</td><td>{$notMentionedErrorStart}{$results['nNotMentioned']} of {$results['nMilestones']}{$notMentionedErrorEnd}\n</td></tr>";
            }
            $details .= "</table>";
        }
        $details .= "$errorMsg</td></tr>";
        return $details;
	}
	
	function findMilestones($project, $report){
	    $reportType = $this->getAttr('reportType', 'NIReport');
	    foreach($report->sections as $section){
	        if(($section->sec == RES_MILESTONES && ($reportType == "NIReport")) ||
	           ($section->sec == HQP_MILESTONES && ($reportType == "HQPReport"))){
	            foreach($section->items as $item){
	                if($item->id == "projects"){
	                    foreach($item->items as $milestoneItem){
	                        if($milestoneItem->projectId == $project->getId() && $milestoneItem->id == "project_head"){
	                            foreach($milestoneItem->items as $toggleItem){
	                                if($toggleItem->id == "milestones"){
	                                    return array('nComplete' => $toggleItem->getNMilestonesComplete(),
	                                                 'nCommented' => $toggleItem->getNMilestonesCommented(),
	                                                 'nInvolved' => $toggleItem->getNMilestonesInvolved(),
	                                                 'nNotInvolved' => $toggleItem->getNMilestonesNotInvolved(),
	                                                 'nNotMentioned' => $toggleItem->getNMilestonesNotMentioned(),
	                                                 'nMilestones' => count($toggleItem->getData()));
	                                }
	                            }
	                        }
	                    }
	                }
	            }
	        }
	    }
	    return array('nComplete' => 0,
	                 'nMilestones' => 0,
	                 'nCommented' => 0,
	                 'nInvolved' => 0,
	                 'nNotInvolved' => 0,
	                 'nNotMentioned' => 0,
	                 'nMilestones' => 0);
	}
	/*
	function findBudget($report){
	    $reportType = $this->getAttr('reportType', 'NIReport');
	    foreach($report->sections as $section){
	        if($section->sec == RES_BUDGET){
	            foreach($section->items as $item){
	                if($item->id == "budget"){
	                    if($item->getBlobValue() != ""){
                            return new Budget("XLS", REPORT2_STRUCTURE, $item->getBlobValue());
                        }
                        return null;
	                }
	            }
	        }
	    }
	}
	*/
}

?>
