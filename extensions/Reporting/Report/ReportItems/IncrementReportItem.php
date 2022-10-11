<?php

class IncrementReportItem extends SelectReportItem {

	function parseOptions(){
	    $person = Person::newFromId($this->blobSubItem);
	    $fecType = $person->getFECType($this->getReport()->year.CYCLE_START_MONTH);
	    switch($fecType){
	        default:
	        case "A1":
	        case "B1":
	        case "B2":
	        case "C1":
	        case "D1":
	        case "E1":
	        case "F1":
	        case "T1":
	        case "T2":
	        case "T3":
	            $options = array("",
	                             "0A", 
	                             "0B", 
	                             "0C", 
	                             "0D",
	                             "0.50", 
	                             "0.75",
	                             "1.00",
	                             "1.25", 
	                             "1.50",
	                             "1.75", 
	                             "2.00",
	                             "2.25", 
	                             "2.50",
	                             "2.75", 
	                             "3.00");
	            break;
	        case "M1":
	        case "N1":
	            $options = array("",
	                             "0A", 
	                             "0B", 
	                             "0C", 
	                             "0D",
	                             "0.50",
	                             "0.75",
	                             "1.00");
	            break;
	    }
	    
	    $salary = $person->getSalary($this->getReport()->year);
	    $increment = "0A";
        $maxSalary = 0;
        switch($fecType){
	        default:
	        case "A1":
	            $increment = Person::getSalaryIncrement($this->getReport()->year, 'assist');
                $maxSalary = Person::getMaxSalary($this->getReport()->year, 'assist');
                break;
	        case "B1":
	        case "B2":
	            $increment = Person::getSalaryIncrement($this->getReport()->year, 'assoc');
                $maxSalary = Person::getMaxSalary($this->getReport()->year, 'assoc');
                break;
	        case "C1":
	            $increment = Person::getSalaryIncrement($this->getReport()->year, 'prof');
                $maxSalary = Person::getMaxSalary($this->getReport()->year, 'prof');
                break;
	        case "D1":
	            $increment = Person::getSalaryIncrement($this->getReport()->year, 'fso2');
                $maxSalary = Person::getMaxSalary($this->getReport()->year, 'fso2');
                break;
	        case "E1":
	            $increment = Person::getSalaryIncrement($this->getReport()->year, 'fso3');
                $maxSalary = Person::getMaxSalary($this->getReport()->year, 'fso3');
                break;
	        case "F1":
	            $increment = Person::getSalaryIncrement($this->getReport()->year, 'fso4');
                $maxSalary = Person::getMaxSalary($this->getReport()->year, 'fso4');
                $minSalary = Person::getMinSalary($this->getReport()->year, 'fso4');
                if($salary > $minSalary + ($increment)*5.0){
                    // Increment decreases for FSO4 after 5.0 
                    $increment = Person::getSalaryIncrement($this->getReport()->year, 'fso3');
                }
                break;
            case "T1":
                $increment = Person::getSalaryIncrement($this->getReport()->year, 'atsec1');
                $maxSalary = Person::getMaxSalary($this->getReport()->year, 'atsec1');
                break;
            case "T2":
                $increment = Person::getSalaryIncrement($this->getReport()->year, 'atsec2');
                $maxSalary = Person::getMaxSalary($this->getReport()->year, 'atsec2');
                break;
            case "T3":
                $increment = Person::getSalaryIncrement($this->getReport()->year, 'atsec3');
                $maxSalary = Person::getMaxSalary($this->getReport()->year, 'atsec3');
                break;
                
	    }
        if($increment > 0 && $maxSalary > 0){
            $exactIncrement = number_format(max(($maxSalary - $salary), 0)/$increment, 2, '.', '');
            if($exactIncrement >= 0){
                if(!in_array($exactIncrement, $options) && $exactIncrement < max($options)){
                    $options[] = $exactIncrement." (PTC)";
                }
            }
            else{
                $options[] = "0.00 (PTC)";
            }
        }
        // Special Increment for COVID-19.  Average of previous 3 increments
        $cna = $person->getCNA($this->getReport()->year);
        if($cna){
            $options[] = "{$cna} (CNA)";
        }
        usort($options, function($a, $b){
            $floatA = floatval($a);
            $floatB = floatval($b);
            if($floatA == $floatB){
                return ($a > $b);
            }
            else{
                return (floatval($a) > floatval($b));
            }
        });
	    return $options;
	}
	
	function render(){
	    global $wgServer, $wgScriptPath;
	    $person = Person::newFromId($this->blobSubItem);
	    $popup = "<div id='previousIncrements{$this->blobSubItem}' title='Previous Increments' style='display:none;'>
	                <center><b>{$person->getNameForForms()}</b></center>
	                <table data-fecid='{getExtra()}' class='wikitable' frame='box' rules='all' width='100%'>
	                    <tr>
	                        <th>FEC Year</th>
	                        <th>Increment</th>
	                        <th>PDF</th>
	                    </tr>";
	                    
	    $report = new DummyReport("ChairTable", $person, null, $this->getReport()->year, true);
	    $report->person = $person;
	    for($year=$this->getReport()->year; $year >= $this->getReport()->year - 6; $year--){
	        $report->year = $year;
	        $pdf = $report->getPDF(false, "Recommendations");
	        $pdfIcon = (isset($pdf[0])) ? "<a class='recommendationPDF' target='_blank' href='$wgServer$wgScriptPath/index.php/Special:ReportArchive?getpdf={$pdf[0]['token']}'><img src='{$wgServer}{$wgScriptPath}/skins/pdf.gif' /></a>" : "";
	        $popup .= @"<tr>
	            <td align='center'>{$year}</td>
	            <td align='center' class='increment{$year}'>{$person->getIncrement($year)}</td>
	            <td align='center'>{$pdfIcon}</td>
	        </tr>";
	    }
	    $popup .= "</table></div>";
	    $popup .= "<script type='text/javascript'>
	        $(document).ready(function(){
	            $('#previousIncrementsLink{$this->blobSubItem}').click(function(){
	                $('#previousIncrements{$this->blobSubItem}').dialog();
	                $('#previousIncrements{$this->blobSubItem}').dialog('show');
	            });
	        });
	    </script>";
	    $replacedText = '&nbsp;<a id="previousIncrementsLink'.$this->blobSubItem.'" title="Previous Increments" class="tooltip" style="cursor:pointer;display:inline-block;vertical-align:bottom;"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"><path d="M13 3c-4.97 0-9 4.03-9 9H1l3.89 3.89.07.14L9 12H6c0-3.87 3.13-7 7-7s7 3.13 7 7-3.13 7-7 7c-1.93 0-3.68-.79-4.94-2.06l-1.42 1.42C8.27 19.99 10.51 21 13 21c4.97 0 9-4.03 9-9s-4.03-9-9-9zm-1 5v5l4.28 2.54.72-1.21-3.5-2.08V8H12z"/></svg></a>'.$popup;
	    $this->value = str_replace('{$item}', '{$item}'.$replacedText, $this->value);
	    $this->value = str_replace('{$value}', '{$value}'.$replacedText, $this->value);
	    parent::render();
	}

}

?>
