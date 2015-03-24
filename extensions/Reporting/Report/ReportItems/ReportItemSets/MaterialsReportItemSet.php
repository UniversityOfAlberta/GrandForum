<?php

class MaterialsReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        $person = Person::newFromId($this->personId);
        $type = $this->getAttr('subType', 'PNI');
        $subs = $person->getEvaluates($type);
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
