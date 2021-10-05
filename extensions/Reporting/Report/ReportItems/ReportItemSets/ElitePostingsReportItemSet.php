<?php

class ElitePostingsReportItemSet extends ReportItemSet {

    var $projectType = "";

    function getData(){
        $data = array();
        $postings = ElitePosting::getAllPostings();
        if(is_array($postings)){
            foreach($postings as $posting){
                if($posting->visibility == "Accepted"){
                    if($this->projectType == "" || ($this->projectType == $posting->getType())){
                        $tuple = self::createTuple();
                        $tuple['product_id'] = $posting->getId();
                        $data[] = $tuple;
                    }
                }
            }
        }
        return $data;
    }

}

?>
