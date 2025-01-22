<?php

/**
 * @package GrandObjects
 */

class LIMSOpportunityPmm extends BackboneModel {

    static $requestIds = null;

    var $id;
    var $contact;
    var $owner;
    var $description;
    var $files = array();
    
    static function generateRequestId(){
        if(self::$requestIds == null){
            $data = DBFunctions::select(array('grand_pmm_opportunity'),
                                        array('id', 'date'),
                                        array(),
                                        array('date' => 'ASC'));
            $lastYear = "0000";
            foreach($data as $row){
                $year = substr($row['date'], 0, 4);
                if($lastYear != $year){
                    $increment = 1;
                }
                $number = sprintf('%03d', $increment);
                self::$requestIds[$row['id']] = "GIS{$year}-{$number}";
                
                $increment++;
                $lastYear = $year;
            }
        }
    }

    static function newFromId($id){
        $data = DBFunctions::select(array('grand_pmm_opportunity'),
                                    array('*'),
                                    array('id' => $id));
        $opportunity = new LIMSOpportunityPmm($data);
        return $opportunity;
    }
    
    static function newFromProjectId($id, $start_date="0000-00-00", $end_date="2100-01-01"){
        $data = DBFunctions::select(array('grand_pmm_opportunity'),
                                    array('*'),
                                    array('project' => $id));
        $opportunities = array();
        foreach($data as $row){
            $opportunity = new LIMSOpportunityPmm(array($row));
        }
        return $opportunities;
    }

    static function getOpportunities($contact_id){
        $data = DBFunctions::select(array('grand_pmm_opportunity'),
                                    array('*'),
                                    array('contact' => $contact_id));
        $opportunities = array();
        foreach($data as $row){
            $opportunity = new LIMSOpportunityPmm(array($row));
            if($opportunity->isAllowedToView()){
                $opportunities[] = $opportunity;
            }
        }
        return $opportunities;
    }

    function __construct($data){
        global $wgServer, $wgScriptPath;
        if(count($data) > 0){
            $this->id = $data[0]['id'];
            $this->contact = $data[0]['contact'];
            $this->owner = $data[0]['owner'];
            $this->description = $data[0]['description'];
            $files = DBFunctions::select(array('grand_lims_files'),
                                         array('id', 'filename', 'type'),
                                         array('opportunity_id' => $this->id));
            foreach($files as $file){
                $file['data'] = '';
                $file['url'] = "{$wgServer}{$wgScriptPath}/index.php?action=api.limsopportunitypmm/{$this->id}/files/{$file['id']}";
                $this->files[] = $file;
            }
        }
    }

    function getId(){
        return $this->id;
    }

    function getContact(){
        return LIMSContactPmm::newFromId($this->contact);
    }

    function getPerson(){
        return Person::newFromId($this->owner);
    }

    function getOwner(){
        return $this->owner;
    }
    

    function getDescription(){
        return $this->description;
    }

    function getTasks(){
        return LIMSTaskPmm::getTasks($this->getId());
    }

    function getFiles(){
        return $this->files;
    }
    function getFile($id){
        if($this->isAllowedToView()){
            $file = DBFunctions::select(array('grand_lims_files'),
                                        array('*'),
                                        array('id' => $id,
                                              'opportunity_id' => $this->id));
            return @$file[0];
        }
        return "";
    }

    function isAllowedToEdit(){
        return ($this->getContact()->isAllowedToEdit() || $this->getPerson()->isMe());
    }
    
    function isAllowedToView(){
        return $this->getContact()->isAllowedToView();
    }
    
    static function isAllowedToCreate(){
        return LIMSContactPmm::isAllowedToCreate();
    }

    function toArray(){
        if($this->isAllowedToView()){
            self::generateRequestId();
            $person = $this->getPerson();
            $owner = array('id' => $person->getId(),
                           'name' => $person->getNameForForms(),
                           'url' => $person->getUrl());
            $json = array('id' => $this->getId(),
                          'requestId' => self::$requestIds[$this->getId()],
                          'contact' => $this->getContact()->getId(),
                          'owner' => $owner,
                          'description' => $this->getDescription(),
                          'files' => $this->getFiles(),
                          'isAllowedToEdit' => $this->isAllowedToEdit());
            return $json;
        }
        return array();
    }

    function create(){
        if(self::isAllowedToCreate()){
            DBFunctions::insert('grand_pmm_opportunity',
                                array('contact' => $this->contact,
                                      'owner' => $this->owner,
                                      'description' => $this->description));
            $this->id = DBFunctions::insertId();
            $this->uploadFiles();
            self::$requestIds = null;
        }
    }

    function update(){
        if($this->isAllowedToEdit()){
            $newProducts = array();
            DBFunctions::update('grand_pmm_opportunity',
                                array('contact' => $this->contact,
                                      'owner' => $this->owner,
                                      'description' => $this->description),
                                array('id' => $this->id));
            $this->uploadFiles();
            self::$requestIds = null;
        }
    }

    function delete(){
        if($this->isAllowedToEdit()){
            DBFunctions::delete('grand_pmm_opportunity',
                                array('id' => $this->id));
            DBFunctions::delete('grand_lims_task',
                                array('opportunity' => $this->id));
            DBFunctions::delete('grand_lims_files',
                                array('opportunity_id' => $this->id));
            $this->id = "";
        }
    }

    function uploadFiles(){
        foreach($this->files as $file){
            if($file->data != ''){
                DBFunctions::insert('grand_lims_files',
                                    array('opportunity_id' => $this->id,
                                          'filename' => $file->filename,
                                          'type' => $file->type,
                                          'data' => $file->data));
            }
            else if(isset($file->delete) && $file->delete == true){
                DBFunctions::delete('grand_lims_files',
                                    array('id' => $file->id));
            }
        }
    }

    function exists(){
        return ($this->getId() > 0);
    }

    function getCacheId(){
        global $wgSitename;
    }
}
?>
