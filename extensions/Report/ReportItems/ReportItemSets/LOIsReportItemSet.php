<?php

class LOIsReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        $person = Person::newFromId($this->personId);
        $type = $this->getAttr('subType', 'ALL_LOI');
        
        $subs = array();
        if($type == "LOI"){
            $subs = $person->getEvaluates($type);
        }
        else if($type == "ALL_LOI"){
            $subs = LOI::getAllLOIs();
        }
        else if($type == "OPT_LOI"){
            $assigned = $person->getEvaluates('LOI');
            $assigned_ids = array();
            foreach($assigned as $a){
                $assigned_ids[] = $a->getId();
            }

            $nonconf = LOI::getNonConflictingLOIs($person->getId());
            $count = 0;
            foreach($nonconf as $loi){
                if(!in_array($loi->getId(), $assigned_ids)){
                    $subs[] = $loi; 
                    $count++;
                }

                if($count > 10){
                    break;
                }
            }
        }

        if(is_array($subs)){
            foreach($subs as $sub){
                $id = $sub->getId();
                $tuple = self::createTuple();
                $tuple['project_id'] = $id;
                // $sql = "SELECT * FROM grand_loi WHERE id={$id}";
                // $res = DBFunctions::execSQL($sql);
                //$data[] = $res[0];
                $data[] = $tuple;
            }
        }
        return $data;
    }

    function getNComplete(){
        $type = $this->getAttr('subType', 'LOI');

        if($type == 'OPT_LOI'){
            return 0;
        }
        else{
            return parent::getNComplete();
        }
    }

    // function getNFields(){
    //     return ;
    // }

}

?>
