<?php

class AllMaterialsReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        //$person = Person::newFromId($this->personId);
        
        $type = $this->getAttr('subType', 'PNI');
        if($type == 'PNI' || $type == 'CNI'){
            //$subs = Person::getAllPeople($type);
            $year = (REPORTING_YEAR == date('Y'))? REPORTING_YEAR-1 : REPORTING_YEAR;
            $subs = Person::getAllEvaluates($type, $year);
            $sorted = array();
            foreach ($subs as $s){
                $rev_name = $s->getReversedName();
                $sorted["{$rev_name}"] = $s;
            }
            ksort($sorted);
            $subs = $sorted;
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
