<?php

class ProgressReportItem extends StaticReportItem {

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
	    $reportType = $this->getAttr('reportType', 'HQPReport');
        $person = Person::newFromId($this->personId);
        $project = $this->getReport()->project;
        $report = new DummyReport($reportType, $person, $project);
        $nEditableSections = 0;
        $nComplete = 0;
        $nFields = 0;
        $limit = 0;
        $actualChars = 0;
        $nExceeding = 0;
        $nEmpty = 0;
        $nTextareas = 0;
        $sections = array();
        foreach($report->sections as $section){
            if($section instanceof EditableReportSection && !$section->private){
                if($section->reportCharLimits){
                    if(count($section->number) > 0){
                        $numbers = array();
                        foreach($section->number as $n){
                            $numbers[] = AbstractReport::rome($n);
                        }
                        $sections[] = implode(', ', $numbers);
                    }
                    $nEditableSections++;
                    $limit += $section->getLimit();
                    $actualChars += $section->getActualNChars();
                    $nExceeding += $section->getExceedingFields();
                    $nEmpty += $section->getEmptyFields();
                    $nTextareas += $section->getNTextareas();
                    $nComplete += $section->getNComplete();
                    $nFields += $section->getNFields();
                }
            }
        }
        $rowspan = 3;
        if($limit > 0){
            $percentChars = number_format(($actualChars/max(1, $limit)*100), 0);
        }
        else{
            $rowspan--;
        }
        $errorChars = "";
        if($nExceeding > 0){
            $rowspan++;
            $plural = "s";
            if($nTextareas == 1){
                $plural = "";
            }    
            $errorChars .= "<tr><td><span class='inlineError'>{$nExceeding} of the {$nTextareas}</span> field{$plural} exceeds maximum allowed characters\n</td></tr>";
        }
        if($nEmpty > 0){
            $rowspan++;
            $plural = "s";
            if($nTextareas == 1){
                $plural = "";
            }  
            $errorChars .= "<tr><td><span class='inlineWarning'>{$nEmpty} of the {$nTextareas}</span> field{$plural} contain no text\n</td></tr>";
        }
        $plural = "s";
        if(count($sections) <= 1){
            $plural = "";
        }
        $details = "<tr valign='top'><td rowspan='$rowspan' style='white-space:nowrap;width:1%;'><b>Report Status</b></td><td valign='top' style='white-space:nowrap;max-width:500px;'>(Section{$plural} ".implode(", ", $sections).")</td></tr>";
        if($limit > 0){
            $details .= "<tr><td>≈$percentChars% of maximum allowable characters (overall)\n</td></tr>";
        }
        $plural = "s";
        if($nTextareas == 1){
            $plural = "";
        }
        $details .= "<tr><td>{$nComplete} of the {$nTextareas} field{$plural} include text\n</td></tr>";
        $details .= "$errorChars";
        return $details;
	}
}

?>
