<?php

class PersonNewGrantsReportItemSet extends PersonGrantsReportItemSet {
    
    function getGrants(){
        $grants = parent::getGrants();
        $start = $this->getAttr('start', CYCLE_START);
        $ret = array();
        if(is_array($grants)){
            foreach($grants as $key => $grant){
                if($grant->getStartDate() >= $start){
                    $ret[$key] = $grant;
                }
            }
        }
        return $ret;
    }

}

?>
