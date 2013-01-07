<?php

class ProjectMaterialsReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        $person = Person::newFromId($this->personId);
        
        $subs = $person->getEvaluateProjects();
        
        if(is_array($subs)){
            foreach($subs as $sub){
                $tuple = self::createTuple();
                $tuple['project_id'] = $sub->getId();
                $data[] = $tuple;
            }
        }
        return $data;
    }

}

?>
