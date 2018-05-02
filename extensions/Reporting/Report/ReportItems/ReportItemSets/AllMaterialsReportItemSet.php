<?php

class AllMaterialsReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        //$person = Person::newFromId($this->personId);
        
        $type = $this->getAttr('subType', 'NI');
        $class = $this->getAttr('class', 'Person');
        $year = $this->getReport()->year;
        if($type != 'SAB' && $type != 'Project' && $class != "Project"){
            $subs = Person::getAllEvaluates($type, $year, $class);
            $sorted = array();
            foreach ($subs as $s){
                $rev_name = $s->getReversedName()."_".$s->getId();
                $sorted["{$rev_name}"] = $s;
            }
            ksort($sorted);
            $subs = $sorted;
        }
        else {
            $subs = Person::getAllEvaluates($type, $year, $class);
            $sorted = array();
            foreach ($subs as $s){
                $name = $s->getName()."_".$s->getId();
                $sorted["{$name}"] = $s;
            }
            ksort($sorted);
            $subs = $sorted;
        }
        if(is_array($subs)){
            foreach($subs as $sub){
                $tuple = self::createTuple();
                if($type == "Project" || $type == "SAB" || $class == "Project"){
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
