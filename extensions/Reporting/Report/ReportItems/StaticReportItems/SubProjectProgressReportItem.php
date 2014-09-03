<?php

class SubProjectProgressReportItem extends StaticReportItem {

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
        $item = str_replace("≈","","$details");
        $item = $this->processCData($item);
		$wgOut->addHTML($item);
	}
	
	function getTableHTML(){
	    $project = $this->getReport()->project;
	    $subs = $project->getSubProjects();
	    $noChamps = 0;
	    $nSubs = 0;
	    foreach($subs as $sub){
	        $champs = $sub->getChampions();
	        if(count($champs) == 0){
	            $noChamps++;
	        }
	        $nSubs++;
	    }
        $details = "";
        if($noChamps > 0){
            $details = "<tr valign='top'><td style='white-space:nowrap;width:1%;'><b>Sub-Project Status</b></td><td><span class='inlineError'>$noChamps of $nSubs Sub-Projects have no Champions\n</span></td></tr>";
        }
        return $details;
	}
}

?>
