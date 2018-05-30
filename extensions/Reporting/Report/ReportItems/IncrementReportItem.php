<?php

class IncrementReportItem extends SelectReportItem {

	function parseOptions(){
	    $person = Person::newFromId($this->blobSubItem);
	    switch($person->getFECType($this->getReport()->year.CYCLE_END_MONTH)){
	        default:
	        case "A1":
	        case "B1":
	        case "B2":
	        case "C1":
	        case "D1":
	        case "E1":
	        case "F1":
	            $options = array("0.00", 
	                             "0.50", 
	                             "1.00", 
	                             "1.50", 
	                             "2.00", 
	                             "2.50", 
	                             "3.00");
	            break;
	        case "M1":
	        case "N1":
	            $options = array("0.00", 
	                             "0.50", 
	                             "1.00");
	            break;
	    }
	    return $options;
	}

}

?>
