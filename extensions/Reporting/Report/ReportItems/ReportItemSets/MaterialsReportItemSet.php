<?php

class MaterialsReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        $person = Person::newFromId($this->personId);
        $type = $this->getAttr('subType', NI);
        $class = $this->getAttr('class', 'Person');
        $subs = $person->getEvaluates($type, $this->getReport()->year, $class);
        if(is_array($subs)){
            foreach($subs as $sub){
                $tuple = self::createTuple();
                if($type == "Project" || $type == "SAB" || $class == "Project"){
                    $tuple['project_id'] = $sub->getId();
                    $name = $sub->getName()."_".$sub->getId();
                }
                else{
                    $tuple['person_id'] = $sub->getId();
                    $name = $sub->getReversedName()."_".$sub->getId();
                }
                $data[$name] = $tuple;
            }
            ksort($data);
        }
        return $data;
    }

}

?>
