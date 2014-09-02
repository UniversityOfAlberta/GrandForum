<?php

class PersonProjectReportItemSet extends ReportItemSet {

    function getData(){
        $phase = $this->getAttr("phase");
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
                if(substr($proj->getCreated(), 0, 10) <= REPORTING_NCE_START && !$proj->isSubProject()){
                    if($phase == "" || $proj->getPhase() == $phase){
                        $tuple = self::createTuple();
                        $tuple['project_id'] = $proj->getId();
                        $data[] = $tuple;
                    }
                }
            }
        }
        return $data;
    }

}

?>
