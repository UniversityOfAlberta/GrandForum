<?php

/**
 * @package GrandObjects
 */

class EliteProfile extends BackboneModel {
    
    var $person;
    var $pdf;
    
    static function newFromUserId($userId){
        $userId = DBFunctions::escape($userId);
        $data = DBFunctions::execSQL("SELECT user_id, report_id
                                      FROM grand_pdf_report
                                      WHERE type = 'RPTP_ELITE'
                                      AND user_id = '$userId'
                                      ORDER BY report_id DESC");
         return EliteProfile($data);
    }
    
    static function getAllProfiles(){
        $data = DBFunctions::execSQL("SELECT t.user_id, t.report_id
                                      FROM (SELECT user_id, report_id
                                            FROM grand_pdf_report
                                            WHERE type = 'RPTP_ELITE'
                                            ORDER BY report_id DESC) t
                                      GROUP BY t.user_id");
        $profiles = array();
        foreach($data as $row){
            $profiles[] = new EliteProfile(array($row));
        }
        return $profiles;
    }
    
    function EliteProfile($data){
        if(count($data) > 0){
            $row = $data[0];
            $this->person = Person::newFromId($row['user_id']);
            $this->pdf = PDF::newFromId($row['report_id']);
        }
    }
    
    function toArray(){
        return array('id' => $this->person->getId(),
                     'user' => $this->person->toSimpleArray(),
                     'status' => $this->getBlobValue('STATUS'),
                     'pdf' => $this->pdf->getUrl(),
                     'created' => $this->pdf->getTimestamp());
    }
    
    function create(){
        
    }
    
    function update(){
        
    }
    
    function delete(){
        
    }
    
    function exists(){
    
    }
    
    function getCacheId(){
        
    }
    
    function getBlobValue($blobItem){
        $year = 0; // Don't have a year so that it remains the same each year
        $personId = $this->person->getId();
        $projectId = 0;
        
        $blb = new ReportBlob(BLOB_TEXT, $year, $personId, $projectId);
        $addr = ReportBlob::create_address('RP_ELITE', 'PROFILE', $blobItem, 0);
        $result = $blb->load($addr);
        $data = $blb->getData();
        
        return $data;
    }
    
    function saveBlobValue($blobItem, $value){
        $year = 0; // Don't have a year so that it remains the same each year
        $personId = $this->person->getId();
        $projectId = 0;
        
        $blb = new ReportBlob(BLOB_TEXT, $year, $personId, $projectId);
        $addr = ReportBlob::create_address('RP_ELITE', 'PROFILE', $blobItem, 0);
        $blb->store($value, $addr);
    }
    
}

?>
