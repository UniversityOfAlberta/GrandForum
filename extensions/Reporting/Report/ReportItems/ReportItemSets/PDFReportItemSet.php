<?php

class PDFReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        $person = Person::newFromId($this->personId);
        $type = $this->getAttr('pdf', "");
        $year = $this->getAttr("year", REPORTING_YEAR);
        $rows = DBFunctions::execSQL("SELECT r.*, i.sub_id FROM `grand_pdf_report` r LEFT JOIN `grand_pdf_index` i 
                                      ON i.report_id = r.report_id
                                      WHERE r.`type` = '{$type}' AND
                                            r.`year` = '{$year}'
                                      GROUP BY r.user_id, i.sub_id");
        foreach($rows as $row){
            $tuple = self::createTuple();
            $tuple['person_id'] = $row['user_id'];
            $tuple['project_id'] = ($row['sub_id'] == null) ? "0" : $row['sub_id'];
            $data[] = $tuple;
        }
        return $data;
    }

}

?>
