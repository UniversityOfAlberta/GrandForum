<?php

class PDFReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        $person = Person::newFromId($this->personId);
        $type = $this->getAttr('pdf', "");
        $year = $this->getAttr("year", REPORTING_YEAR);
        $rows = DBFunctions::execSQL("SELECT user_id, proj_id FROM `grand_pdf_report`
                                      WHERE type = '{$type}' AND
                                            year = '{$year}'
                                      GROUP BY user_id, proj_id");
        foreach($rows as $row){
            $tuple = self::createTuple();
            $tuple['person_id'] = $row['user_id'];
            $tuple['project_id'] = ($row['proj_id'] == null) ? "0" : $row['proj_id'];
            $data[] = $tuple;
        }
        return $data;
    }

}

?>
