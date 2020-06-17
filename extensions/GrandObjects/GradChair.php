<?php

/**
 * @package GrandObjects
 */
class GradChair extends BackboneModel {
    
    var $hqpId;
    var $hqp;
    var $uniId;
    var $background;
    var $background_notes;
    var $meetings;
    var $meetings_notes;
    var $ethics;
    var $ethics_notes;
    var $courses;
    var $courses_notes;
    var $notes;
    
    static function getAllByDepartment($dept, $start=REPORTING_CYCLE_START, $end=REPORTING_CYCLE_END){
        $data = array();
        $allPeople = Person::getAllPeopleInDepartment($dept, $start, $end);
        foreach($allPeople as $person){
            if($person->isRoleDuring(HQP, $start, $end)){
                $universities = $person->getUniversitiesDuring($start, $end);
                foreach($universities as $university){
                    if(in_array(strtolower($university['position']), Person::$studentPositions['grad']) &&
                       $university['department'] == $dept && 
                       $university['university'] == "University of Alberta"){
                        $data[] = new GradChair($person->getId(), $university);
                        break;
                    }
                }
            }
        }
        return $data;
    }
    
    static function newFromId($hqpId, $start=REPORTING_CYCLE_START, $end=REPORTING_CYCLE_END){
        $person = Person::newFromId($hqpId);
        if($person->isRoleDuring(HQP, $start, $end)){
            $universities = $person->getUniversitiesDuring($start, $end);
            foreach($universities as $university){
                if(in_array(strtolower($university['position']), Person::$studentPositions['grad'])){
                    return new GradChair($person->getId(), $university);
                    break;
                }
            }
        }
        return null;
    }

    function GradChair($hqpId, $university){
        $this->hqpId = $hqpId;
        $this->getHQP();
        $this->university = $university;
        $this->background = $this->getBlob("BACKGROUND");
        $this->background_notes = $this->getBlob("BACKGROUND_NOTES");
        $this->meetings = $this->getBlob("MEETINGS");
        $this->meetings_notes = $this->getBlob("MEETINGS_NOTES");
        $this->ethics = $this->getBlob("ETHICS");
        $this->ethics_notes = $this->getBlob("ETHICS_NOTES");
        $this->courses = $this->getBlob("COURSES");
        $this->courses_notes = $this->getBlob("COURSES_NOTES");
        $this->notes = $this->getBlob("NOTES");
    }
    
    function getHQP(){
        if($this->hqp == null){
            $this->hqp = Person::newFromId($this->hqpId);
        }
        return $this->hqp;
    }
    
    function getSupervisors(){
        $relations = Relationship::newFromUserUniversity($this->university['id']);
        $supervisors = array();
        foreach($relations as $relation){
            if($relation->getType() == SUPERVISES || 
               $relation->getType() == CO_SUPERVISES){
                $person = $relation->getUser1();
                $supervisors[$person->getId()] = $person;
            }
        }
        return $supervisors;
    }
    
    function create(){
        $this->update();
    }
    
    function update(){
        $this->setBlob("BACKGROUND", $this->background);
        $this->setBlob("BACKGROUND_NOTES", $this->background_notes);
        $this->setBlob("MEETINGS", $this->meetings);
        $this->setBlob("MEETINGS_NOTES", $this->meetings_notes);
        $this->setBlob("ETHICS", $this->ethics);
        $this->setBlob("ETHICS_NOTES", $this->ethics_notes);
        $this->setBlob("COURSES", $this->courses);
        $this->setBlob("COURSES_NOTES", $this->courses_notes);
        $this->setBlob("NOTES", $this->notes);
    }
    
    function delete(){
    
    }
    
    function toArray(){
        return array('hqpId' => $this->hqpId,
                     'hqp' => array('name' => $this->getHQP()->getNameForForms(),
                                    'email' => $this->getHQP()->getEmail(),
                                    'url' => $this->getHQP()->getUrl()),
                     'program' => $this->university['position'],
                     'supervisors' => (new Collection($this->getSupervisors()))->pluck("getNameForForms()"),
                     'background' => $this->background,
                     'background_notes' => $this->background_notes,
                     'meetings' => $this->meetings,
                     'meetings_notes' => $this->meetings_notes,
                     'ethics' => $this->ethics,
                     'ethics_notes' => $this->ethics_notes,
                     'courses' => $this->courses,
                     'courses_notes' => $this->courses_notes,
                     'notes' => $this->notes);
    }
    
    function exists(){
        return true;
    }
    
    function getCacheId(){
        
    }
    
    function getBlob($item){
        $blob = new ReportBlob(BLOB_TEXT, 0, 0, 0);
	    $blob_address = ReportBlob::create_address("RP_GRAD_CHAIR", "CHAIR", $item, $this->hqpId);
	    $blob->load($blob_address);
	    return $blob->getData();
	}
	
	function setBlob($item, $value){
        $blob = new ReportBlob(BLOB_TEXT, 0, 0, 0);
	    $blob_address = ReportBlob::create_address("RP_GRAD_CHAIR", "CHAIR", $item, $this->hqpId);
	    $blob->store($value, $blob_address);
	}

}

?>
