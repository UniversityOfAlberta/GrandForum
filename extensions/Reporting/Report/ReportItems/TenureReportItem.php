<?php

class TenureReportItem extends SelectReportItem {
	
	function parseOptions(){
	    $person = Person::newFromId($this->blobSubItem);
	    switch($person->getFECType($this->getReport()->year.CYCLE_END_MONTH)){
	        default:
	        case "A1":
	        case "B1":
	        case "B2":
	        case "C1":
	            $options = array("n/a", 
	                             "i recommend promotion to full professor", 
	                             "i do not support the staff member's promotion to full professor");
	            break;
	        case "D1":
	            $options = array("n/a", 
	                             "i recommend promotion to faculty service officer iii");
	            break;
	        case "E1":
	            $options = array("n/a", 
	                             "i recommend promotion to faculty service officer iv");
	            break;
	        case "F1":
	            $options = array("n/a");
	            break;
	        case "M1":
	        case "N1":
	            $options = array("n/a");
	    }
	    return $options;
	}

}

?>
