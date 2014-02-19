<?php

class PLProgressReportItem extends StaticReportItem {

	function render(){
	    global $wgOut;
        $details = $this->getTableHTML();
        $item = "<div id='{$this->personId}_progress_details'>$details</div>";
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
        $project = Project::newFromId($this->projectId);
        
        if($project->getPhase() > 1){
            return "";
        }
        
        $nItems = 0;
        $total = 0;
        foreach($this->getReport()->sections as $section){
            foreach($section->items as $item){
                $tmpArray = $this->findAutoComplete($item);
	            $nItems += $tmpArray['nItems'];
	            $total += $tmpArray['total'];
            }
        }
        $reportItemSet = new ProjectPeopleNoLeadersReportItemSet();
        $reportItemSet->setPersonId($this->personId);
		$reportItemSet->setProjectId($this->projectId);
		$reportItemSet->setMilestoneId($this->milestoneId);
		$nis = $reportItemSet->getData();
		$nPNIs = 0;
		$nCNIs = 0;
		$totalPNIs = 0;
		$totalCNIs = 0;
		
		$details = "";
		
		foreach($nis as $nid){
		    $ni = Person::newFromId($nid['person_id']);
		    if($ni->isRoleDuring(PNI, REPORTING_CYCLE_START, REPORTING_CYCLE_END)){
		        $comment = $this->findComments($ni);
		        if($comment != null && $comment != ""){
		            $totalPNIs++;
		        }
		        $nPNIs++;
		    }
		    else if($ni->isRoleDuring(CNI, REPORTING_CYCLE_START, REPORTING_CYCLE_END)){
		        $comment = $this->findComments($ni);
		        if($comment != null && $comment != ""){
		            $totalCNIs++;
		        }
		        $nCNIs++;
		    }
		}
		
        $details .= "<tr><td><b>Milestones</b></td>";
        $details .= "<td>{$total} of the {$nItems} milestones have been cited in your milestone status overview\n</td></tr>";
        $details .= "<tr><td valign='top' style='white-space:nowrap;'><b>NI Comments</b></td>";
        $pniComments = "{$totalPNIs} of the {$nPNIs}";
        $cniComments = "{$totalCNIs} of the {$nCNIs}";
        
        if($totalPNIs < $nPNIs){
            $details .= "<td><span class='inlineError'>$pniComments</span> PNIs; ";
        }
        else{
            $details .= "<td>$pniComments PNIs; ";
        }
        if($totalCNIs < $nCNIs){
            $details .= "<span class='inlineError'>$cniComments</span> CNIs\n</td></tr>";
        }
        else{
            $details .= "$cniComments CNIs\n</td></tr>";
        }
        return $details;
	}
	
	function findComments($person){
	    foreach($this->getReport()->sections as $section){
	        if($section->sec == LDR_NICOMMENTS){
	            foreach($section->items as $item){
	                if($item->id == "members_noleaders"){
	                    foreach($item->items as $commentItem){
	                        if($commentItem->items[0]->personId == $person->getId()){
	                            return trim($commentItem->items[0]->getBlobValue());
	                        }
	                    }
	                }
	            }
	        }
	    }
	}
	
	function findAutoComplete($item){
	    $total = array('nItems' => 0, 'total' => 0);
	    if($item instanceof ReportItemSet){
	        foreach($item->items as $it){
	            $tmpArray = $this->findAutoComplete($it);
	            $total['nItems'] += $tmpArray['nItems'];
	            $total['total'] += $tmpArray['total'];
	        }
	    }
	    else if($item instanceof AutoCompleteTextareaReportItem && $item->id == "milestone_desc"){
	        $reportItemSet = $item->getSet();
	        $index = $item->getAttr("index", "");
	        $total['nItems'] += count($reportItemSet->getData());
	        $value = nl2br($item->getBlobValue());
	        foreach($reportItemSet->getData() as $tuple){
		        $staticValue = new StaticReportItem();
		        $staticValue->setPersonId($tuple['person_id']);
		        $staticValue->setProjectId($tuple['project_id']);
		        $staticValue->setMilestoneId($tuple['milestone_id']);
		        $staticValue->setValue('{$'.$index.'}');
		        $id = $staticValue->processCData("");
		        
		        $val = preg_match("/(@\[{$id}.*])([^0-9]+?|$)/", $value);
		        if($val == 1){
		            $total['total']++;
		        }
		    }

	    }
	    return $total;
	}
}

?>
