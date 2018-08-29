<?php

class IncrementReportItem extends SelectReportItem {

	function parseOptions(){
	    $person = Person::newFromId($this->blobSubItem);
	    $fecType = $person->getFECType($this->getReport()->year.CYCLE_END_MONTH);
	    switch($fecType){
	        default:
	        case "A1":
	        case "B1":
	        case "B2":
	        case "C1":
	        case "D1":
	        case "E1":
	        case "F1":
	            $options = array("0A", 
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
	            $options = array("0A", 
	                             "0B", 
	                             "0C", 
	                             "0D",
	                             "0.50",
	                             "0.75",
	                             "1.00");
	            break;
	    }
	    
	    $salary = $person->getSalary($this->getReport()->year-1);
	    $increment = "0A";
        $maxSalary = 0;
        switch($fecType){
	        default:
	        case "A1":
	            $increment = Person::getSalaryIncrement($this->getReport()->year-1, 'assist');
                $maxSalary = Person::getMaxSalary($this->getReport()->year-1, 'assist');
                break;
	        case "B1":
	        case "B2":
	            $increment = Person::getSalaryIncrement($this->getReport()->year-1, 'assoc');
                $maxSalary = Person::getMaxSalary($this->getReport()->year-1, 'assoc');
                break;
	        case "C1":
	            $increment = Person::getSalaryIncrement($this->getReport()->year-1, 'prof');
                $maxSalary = Person::getMaxSalary($this->getReport()->year-1, 'prof');
                break;
	        case "D1":
	            $increment = Person::getSalaryIncrement($this->getReport()->year-1, 'fso2');
                $maxSalary = Person::getMaxSalary($this->getReport()->year-1, 'fso2');
                break;
	        case "E1":
	            $increment = Person::getSalaryIncrement($this->getReport()->year-1, 'fso3');
                $maxSalary = Person::getMaxSalary($this->getReport()->year-1, 'fso3');
                break;
	        case "F1":
	            $increment = Person::getSalaryIncrement($this->getReport()->year-1, 'fso4');
                $maxSalary = Person::getMaxSalary($this->getReport()->year-1, 'fso4');
                break;
	    }
        if($increment > 0 && $maxSalary > 0){
            $exactIncrement = number_format(($maxSalary - $salary)/$increment, 2, '.', '');
            
            if($exactIncrement > 0){
                if(!in_array($exactIncrement, $options) && $exactIncrement < max($options)){
                    $options[] = $exactIncrement." (PTC)";
                }
            }
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

}

?>
