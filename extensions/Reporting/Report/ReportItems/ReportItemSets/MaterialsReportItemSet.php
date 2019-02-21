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
                    $tuple['project_id'] = $sub[0]->getId();
                    $tuple['person_id'] = $sub[1];
                    $name = $sub[0]->getName()."_".$sub[0]->getId();
                }
                else{
                    $tuple['person_id'] = $sub[0]->getId();
                    $tuple['project_id'] = $sub[1];
                    $name = $sub[0]->getReversedName()."_".$sub[0]->getId();
                }
                if($sub[1] != 0){
                    $name .= "_{$sub[1]}";
                }
                $data[$name] = $tuple;
            }
            ksort($data);
        }
        return $data;
    }

}

?>
