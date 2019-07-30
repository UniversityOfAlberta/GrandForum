<?php

class AllPeopleWithConferenceApplicationsReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $allPeople = Person::getAllPeople();
        $rows = DBFunctions::execSQL("SELECT user_id
                                      FROM grand_report_blobs
                                      WHERE rp_type = 'RP_CONFERENCE_APPLICATIONS'
                                      AND year = '{$this->getReport()->year}'
                                      GROUP BY user_id");
        foreach($rows as $row){
            $person = Person::newFromId($row['user_id']);
            if($person != null && $person->getId() != 0){
                $tuple = self::createTuple();
                $tuple['person_id'] = $person->getId();
                $data[] = $tuple;
            }
        }
        return $data;
    }
}

?>
