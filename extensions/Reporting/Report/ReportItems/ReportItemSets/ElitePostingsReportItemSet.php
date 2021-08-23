<?php

class ElitePostingsReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        $postings = ElitePosting::getAllPostings();
        if(is_array($postings)){
            foreach($postings as $posting){
                if($posting->visibility == "Accepted"){
                    $tuple = self::createTuple();
                    $tuple['product_id'] = $posting->getId();
                    $data[] = $tuple;
                }
            }
        }
        return $data;
    }

}

?>
