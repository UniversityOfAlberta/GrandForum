<?php

class PromotionReportItem extends SelectReportItem {
	
	function parseOptions(){
	    $person = Person::newFromId($this->blobSubItem);
	    switch($person->getFECType($this->getReport()->year.CYCLE_END_MONTH)){
	        default:
	        case "A1":
	        case "B1":
	        case "B2":
	        case "C1":
	            $options = array("n/a", 
	                             "i recommend that an appointment with tenure be offered", 
	                             "i recommend that the second probationary period be extended by one year",
	                             "i recommend that no further appointment be offered to the staff member",
	                             "i recommend tenure as per clause 12.17 (special recommendation for tenure)");
	            break;
	        case "D1":
	        case "E1":
	        case "F1":
	            $options = array("n/a", 
	                             "i recommend that continuing appointment be offered",
	                             "i recommend that no further appointment be offered");
	            break;
	        case "M1":
	        case "N1":
	            $options = array("n/a");
	    }
	    return $options;
	}

}

?>
