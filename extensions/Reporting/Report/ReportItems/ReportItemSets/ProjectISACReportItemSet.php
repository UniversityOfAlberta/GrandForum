<?php

/**
 * This is sort of a weird class.  
 * ISAC are associated with projects if they have written about them for the year
 */
class ProjectISACReportItemSet extends ReportItemSet {
    
    function getData(){
        $data = array();
        $proj = Project::newFromId($this->projectId);
        if($proj != null){
            $isac = Person::getAllPeopleOn(ISAC, ($this->getReport()->year+1).REPORTING_RMC_MEETING_MONTH);
            foreach($isac as $i => $person){
                $addr = ReportBlob::create_address(RP_ISAC, ISAC_PHASE2, ISAC_PHASE2_COMMENT, 0);
                $blb = new ReportBlob(BLOB_TEXT, $this->getReport()->year, $person->getId(), $proj->getId());
                $result = $blb->load($addr);
                $blobdata = $blb->getData();
                if($blobdata != null){
                    $tuple = self::createTuple();
                    $tuple['person_id'] = $person->getId();
                    $data[$person->getReversedName()] = $tuple;
                }
            }
            ksort($data);
        }
        return $data;
    }
}

?>
