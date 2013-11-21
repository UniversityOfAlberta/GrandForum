<?php

class MindTheGapReportItemSet extends ReportItemSet {

    function getData(){
        $data = array();
        $people = Person::getAllPeople();
        foreach($people as $person){
            $blob = new ReportBlob(BLOB_TEXT, $this->getReport()->year, $person->getId(), 0);
            $include = false;
            if($this->getSection()->sec == SEC_NONE){
                $blob_address = ReportBlob::create_address($this->getReport()->reportType, MTG_MUSIC, MTG_MUSIC, 0);
	            $blob->load($blob_address);
	            $music_data = $blob->getData();
	            
	            $blob_address = ReportBlob::create_address($this->getReport()->reportType, MTG_FIRST_NATIONS, MTG_FIRST_NATIONS, 0);
	            $blob->load($blob_address);
	            $firstnations_data = $blob->getData();
	            
	            $blob_address = ReportBlob::create_address($this->getReport()->reportType, MTG_SOCIAL_PROBLEMS, MTG_SOCIAL_PROBLEMS, 0);
	            $blob->load($blob_address);
	            $socialproblems_data = $blob->getData();
	            
	            $blob_address = ReportBlob::create_address($this->getReport()->reportType, MTG_OTHER, MTG_OTHER, 0);
	            $blob->load($blob_address);
	            $other_data = $blob->getData();
	            
	            if($music_data != null || 
	               $firstnations_data != null || 
	               $socialproblems_data != null ||
	               $other_data != null){
	                $include = true;
	            }
            }
            else{
	            $blob_address = ReportBlob::create_address($this->getReport()->reportType, $this->getSection()->sec, $this->getSection()->sec, 0);
	            $blob->load($blob_address);
	            $blob_data = $blob->getData();
	            if($blob_data != null){
	                $include = true;
	            }
	        }
	        
	        if($include){
	            $tuple = self::createTuple();
	            $tuple['person_id'] = $person->getId();
	            $data[] = $tuple;
	        }
        }
        return $data;
    }

}

?>
