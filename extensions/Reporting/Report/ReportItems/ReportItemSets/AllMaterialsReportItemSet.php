<?php

class AllMaterialsReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        //$person = Person::newFromId($this->personId);
        
        $type = $this->getAttr('subType', 'NI');
        if($type != 'SAB' && $type != 'Project'){
            //$subs = Person::getAllPeople($type);
            $year = $this->getReport()->year;
            $subs = Person::getAllEvaluates($type, $year);
            $sorted = array();
            foreach ($subs as $s){
                $rev_name = $s->getReversedName();
                $sorted["{$rev_name}"] = $s;
            }
            ksort($sorted);
            $subs = $sorted;
        }
        else {
            $subs = Project::getAllProjects();
        }
        if(is_array($subs)){
            foreach($subs as $sub){
                $tuple = self::createTuple();
                if($type == "Project" || $type == "SAB"){
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
