<?php

class PersonOutputsReportItemSet extends ReportItemSet {
    
    function getData(){
        $products = new PersonProductsReportItemSet();
        $grants = new PersonGrantsReportItemSet();
        
        $products->parent = $this->getParent();
        $grants->parent = $this->getParent();
        
        $products->setPersonId($this->personId);
        $products->setProjectId($this->projectId);
        $products->setMilestoneId($this->milestoneId);
        $products->setProductId($this->productId);
        
        $grants->setPersonId($this->personId);
        $grants->setProjectId($this->projectId);
        $grants->setMilestoneId($this->milestoneId);
        $grants->setProductId($this->productId);
        
        $products->attributes = $this->attributes;
        $grants->attributes = $this->attributes;
        
        $data = array();
        foreach($products->getData() as $tuple){
            $tuple['extra'] = 'Product';
            $data[] = $tuple;
        }
        
        foreach($grants->getData() as $tuple){
            $tuple['extra'] = 'Grant';
            
            $data[] = $tuple;
        }
        return $data;
    }

}

?>
