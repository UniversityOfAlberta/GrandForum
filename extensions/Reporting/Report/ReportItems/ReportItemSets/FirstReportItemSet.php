<?php

class FirstReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $parent = $this->getParent();
        while(!($parent instanceof ReportItemSet)){
            $parent = $this->getParent();
        }
        $tuples = $parent->getData();
        $first = false;
        foreach($tuples as $tuple){
            if($this->milestoneId == $tuple['milestone_id'] &&
               $this->projectId == $tuple['project_id'] &&
               $this->personId == $tuple['person_id'] &&
               $this->productId == $tuple['product_id'] &&
               $this->extra == $tuple['extra']){
                $first = true;
            }
            break;    
        }
        if($first){
            $tuple = self::createTuple();
            $data[] = $tuple;
        }
        return $data;
    }
}

?>
