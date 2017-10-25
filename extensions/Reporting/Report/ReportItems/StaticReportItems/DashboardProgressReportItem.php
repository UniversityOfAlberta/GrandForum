<?php

class DashboardProgressReportItem extends StaticReportItem {

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
		
		if($person->getId() == 0){
		    $papers = $project->getPapers("Publication", REPORTING_CYCLE_START, REPORTING_CYCLE_END);
		}
		else{
		    $papers = $person->getPapersAuthored("Publication", REPORTING_CYCLE_START, REPORTING_CYCLE_END, true);
		}
		
		$nPublications = 0;
		$nNoVenue = 0;
		$nNoPages = 0;
		$nNoPublisher = 0;
		foreach($papers as $paper){
		    if(($project == null || $paper->belongsToProject($project)) && $paper->getStatus() == "Published"){
		        $data = $paper->getData();
		        $vn = $paper->getVenue();
		        if($paper->getType() == "Proceedings Paper" && $vn == ""){
                    $nNoVenue++;
                }
                
                if(in_array($paper->getType(), array('Book', 'Collections Paper', 'Proceedings Paper', 'Journal Paper'))){
                    $pg = $paper->getData(array('ms_pages', 'pages'));
                    if (!(strlen($pg) > 0)){
                        $nNoPages++;
                    }
                    $pb = $paper->getData('publisher');
                    if($pb == ''){
                        $nNoPublisher++;
                    }
                }
		        $nPublications++;
		    }
		}
		
		$noVenue = "";
		$noPages = "";
		$noPublisher = "";
		$rowspan = 0;
		if($nNoVenue > 0){
		    if($nNoVenue == 1){
		        $noVenue = "<td>{$nNoVenue} does not have a venue\n</td></tr>";
		    }
		    else{
		        $noVenue = "<td>{$nNoVenue} do not have a venue\n</td></tr>";
		    }
		    $rowspan++;
		}
		if($nNoPages > 0){
		    $tr = "";
		    if($nNoVenue > 0){
		        $tr = "<tr>";
		    }
		    if($nNoPages == 1){
		        $noPages = "$tr<td>{$nNoPages} does not have page information\n</td></tr>";
		    }
		    else{
		        $noPages = "$tr<td>{$nNoPages} do not have page information\n</td></tr>";
		    }
		    $rowspan++;
		}
		if($nNoPublisher > 0){
		    $tr = "";
		    if($nNoPages > 0 || $nNoVenue > 0){
		        $tr = "<tr>";
		    }
		    if($nNoPublisher == 1){
		        $noPublisher = "$tr<td>{$nNoPublisher} does not have a publisher\n</td></tr>";
		    }
		    else{
		        $noPublisher = "$tr<td>{$nNoPublisher} do not have a publisher\n</td></tr>";
		    }
		    $rowspan++;
		}
		$details = "";
        if($rowspan > 0){
            $details .= "<tr><td valign='top' rowspan='$rowspan'><b>Publications</b></td>";
            $details .= "{$noVenue}";
            $details .= "{$noPages}";
            $details .= "{$noPublisher}";
        }
        return $details;
	}
}
?>
