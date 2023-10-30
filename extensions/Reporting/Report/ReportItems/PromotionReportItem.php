<?php

class PromotionReportItem extends SelectReportItem {
	
	function parseOptions(){
	    $person = Person::newFromId($this->blobSubItem);
	    $options = array("n/a");
        switch($person->getFECType($this->getReport()->year."-07-01")){
            case "A1":
            case "B1":
            case "B2":
                // Faculty members (assistants and associates) are eligible for promotion if:
                //   1. salary >= min_salary_prof - increment_rate_professor for that year AND
                //   2. (is_tenured and !is_professor) or is_tenure_selected (this is will be done with javascript)
                if($person->getSalary($this->getReport()->year) >= Person::getMinSalary($this->getReport()->year, 'prof') - Person::getSalaryIncrement($this->getReport()->year, 'assoc') ||
                   $person->getName() == "Matthew.Taylor"){ // TODO: Get Rid of this later
                    $options = array("n/a", 
                                     "i recommend promotion to full professor", 
                                     "i do not support the staff member's promotion to full professor");
                }
                else {
                    $options = array("n/a");
                }
                break;
            case "D1":
                if($person->getSalary($this->getReport()->year) >= Person::getMinSalary($this->getReport()->year, 'fso3') - Person::getSalaryIncrement($this->getReport()->year, 'fso2')){
                    $options = array("n/a", 
                                     "i recommend promotion to faculty service officer iii");
                }
                else {
                    $options = array("n/a");
                }
                break;
            case "E1":
                if($person->getSalary($this->getReport()->year) >= Person::getMinSalary($this->getReport()->year, 'fso4') - Person::getSalaryIncrement($this->getReport()->year, 'fso3')){
                    $options = array("n/a", 
                                     "i recommend promotion to faculty service officer iv");
                }
                else {
                    $options = array("n/a");
                }
                break;
            case "T1": // Assistant Lecturer
                if($person->getSalary($this->getReport()->year) >= Person::getMinSalary($this->getReport()->year, 'atsec2') - Person::getSalaryIncrement($this->getReport()->year, 'atsec1')){
                    $options = array("n/a", 
                                     "i recommend promotion to associate lecturer");
                }
                else {
                    $options = array("n/a");
                }
                break;
            case "T2": // Associate Lecturer
                if($person->getSalary($this->getReport()->year) >= Person::getMinSalary($this->getReport()->year, 'atsec3') - Person::getSalaryIncrement($this->getReport()->year, 'atsec2')){
                    $options = array("n/a", 
                                     "i recommend promotion to full lecturer");
                }
                else {
                    $options = array("n/a");
                }
                break;
            case "T3":
            case "C1":
            case "F1":
            case "M1":
            case "N1":
                $options = array("n/a");
                break;
        }
	    return $options;
	}

}

?>
