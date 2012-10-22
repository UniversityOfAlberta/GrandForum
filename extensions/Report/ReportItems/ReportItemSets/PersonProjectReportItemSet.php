<?php

class PersonProjectReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        $person = Person::newFromId($this->personId);
        if($this->getReport()->topProjectOnly){
            $projects = array($this->getReport()->project);
        }
        else{
            $projects = $person->getProjectsDuring();
        }
        if(is_array($projects)){
            foreach($projects as $proj){
                $tuple = self::createTuple();
                $tuple['project_id'] = $proj->getId();
                $data[] = $tuple;
            }
        }
        return $data;
    }

}

?>
