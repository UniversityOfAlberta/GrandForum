<?php

class NIMindTheGapReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        $people = Person::getAllPeople();
        foreach($people as $person){
            $blob = new ReportBlob(BLOB_TEXT, $this->getReport()->year, $person->getId(), 0);
	        $blob_address = ReportBlob::create_address($this->getReport()->reportType, $this->getSection()->sec, $this->getSection()->sec, 0);
	        $blob->load($blob_address);
	        $blob_data = $blob->getData();
	        if($blob_data != null){
	            $tuple = self::createTuple();
	            $tuple['person_id'] = $person->getId();
	            $data[] = $tuple;
	        }
        }
        return $data;
    }

}

?>
