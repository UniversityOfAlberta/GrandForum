<?php

class SelfReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $value   = $this->getAttr('blobValue'  , '');
        
        $report  = $this->getAttr('reportType' , 0);
        $section = $this->getAttr('blobSection', 0);
        $item    = $this->getAttr('blobItem'   , 0);
        $subItem = $this->getAttr('subItem'    , 0);
        
        $res = DBFunctions::select(array('grand_report_blobs'),
                                   array('user_id'),
                                   array('year'       => EQ($this->getReport()->year),
                                         'rp_type'    => EQ($report),
                                         'rp_section' => EQ($section),
                                         'rp_item'    => EQ($item),
                                         'rp_subitem' => EQ($subItem),
                                         'data'       => EQ($value)));
        foreach($res as $row){
            $userId = $row['user_id'];
            $tuple = self::createTuple();
            $tuple['person_id'] = $userId;
            $data[$userId] = $tuple;
        }
        return array_values($data);
    }
}

?>
