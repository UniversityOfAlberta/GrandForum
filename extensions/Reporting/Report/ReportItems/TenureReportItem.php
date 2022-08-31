<?php

class TenureReportItem extends SelectReportItem {
	
	function parseOptions(){
	    $person = Person::newFromId($this->blobSubItem);
	    if($person->hasTenure($this->getReport()->year."-07-01")){
	        return array("already has tenure");
	    }
	    switch($person->getFECType($this->getReport()->year."-07-01")){
	        case "A1":
	        case "B1":
	            // Faculty eligible for tenure: not tenured AND not a professor. The second condition is superfluous, because all professors have tenure.
	            $options = array("n/a", 
	                             "i recommend that an appointment with tenure be offered", 
	                             "i recommend a second probationary appointment be offered to the staff member",
	                             "i recommend that the second probationary period be extended by one year",
	                             "i recommend that no further appointment be offered to the staff member",
	                             "i recommend tenure as per clause 12.17 (special recommendation for tenure)");
	            break;
	        case "D1":
	        case "E1":
	            // FSO eligible for tenure: not tenured AND not an fso4. The second condition is superfluous, because all fso4 have continuing appointment.
	            $options = array("n/a", 
	                             "i recommend that continuing appointment be offered",
	                             "i recommend that no further appointment be offered");
	            break;
	        default:
	        case "F1":
	        case "B2":
	        case "C1":
	        case "M1":
	        case "N1":
	            $options = array("n/a");
	            break;
	    }
	    return $options;
	}

}

?>
