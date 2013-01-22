<?php

class AllMaterialsReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        //$person = Person::newFromId($this->personId);
        
        $type = $this->getAttr('subType', 'PNI');
        if($type == 'PNI' || $type == 'CNI'){
            $subs = Person::getAllPeople($type);
        }
        else if($type == 'Project'){
            $subs = Project::getAllProjects();
        }
        if(is_array($subs)){
            foreach($subs as $sub){
                $tuple = self::createTuple();
                if($type == "Project"){
                    $tuple['project_id'] = $sub->getId();
                }
                else{
                    $tuple['person_id'] = $sub->getId();
                }
                $data[] = $tuple;
            }
        }
        return $data;
    }

}

?>
