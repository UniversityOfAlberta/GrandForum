<?php

class LOIsReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        $person = Person::newFromId($this->personId);
        $type = $this->getAttr('subType', 'LOI');
        $subs = $person->getEvaluates($type);
        if(is_array($subs)){
            foreach($subs as $id){
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

}

?>
