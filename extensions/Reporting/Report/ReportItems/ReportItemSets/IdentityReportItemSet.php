<?php

class IdentityReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        $data[] = self::createTuple();
        return $data;
    }

}

?>
