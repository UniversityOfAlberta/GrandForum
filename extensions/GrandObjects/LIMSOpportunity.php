<?php

/**
 * @package GrandObjects
 */

class LIMSOpportunity extends BackboneModel {

    static $requestIds = null;

    var $id;
    var $contact;
    var $owner;
    var $project;
    var $userType;
    var $description;
    var $category;
    var $surveyed;
    var $responded;
    var $satisfaction;
    var $status;
    var $date;
    var $files = array();
    var $products = array();
    
    static function generateRequestId(){
        if(self::$requestIds == null){
            $data = DBFunctions::select(array('grand_lims_opportunity'),
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
        $data = DBFunctions::select(array('grand_lims_opportunity'),
                                    array('*'),
                                    array('id' => $id));
        $opportunity = new LIMSOpportunity($data);
        return $opportunity;
    }
    
    static function newFromProjectId($id, $start_date="0000-00-00", $end_date="2100-01-01"){
        $data = DBFunctions::select(array('grand_lims_opportunity'),
                                    array('*'),
                                    array('project' => $id));
        $opportunities = array();
        foreach($data as $row){
            $opportunity = new LIMSOpportunity(array($row));
            if($opportunity->isAllowedToView()){
                if($opportunity->getDate() >= $start_date &&
                   $opportunity->getDate() <= $end_date){
                    $opportunities[] = $opportunity;
                }
            }
        }
        return $opportunities;
    }

    static function getOpportunities($contact_id){
        $data = DBFunctions::select(array('grand_lims_opportunity'),
                                    array('*'),
                                    array('contact' => $contact_id));
        $opportunities = array();
        foreach($data as $row){
            $opportunity = new LIMSOpportunity(array($row));
            if($opportunity->isAllowedToView()){
                $opportunities[] = $opportunity;
            }
        }
        return $opportunities;
    }

    function LIMSOpportunity($data){
        global $wgServer, $wgScriptPath;
        if(count($data) > 0){
            $this->id = $data[0]['id'];
            $this->contact = $data[0]['contact'];
            $this->owner = $data[0]['owner'];
            $this->project = $data[0]['project'];
            $this->userType = $data[0]['user_type'];
            $this->description = $data[0]['description'];
            $this->category = $data[0]['category'];
            $this->surveyed = $data[0]['surveyed'];
            $this->responded = $data[0]['responded'];
            $this->satisfaction = $data[0]['satisfaction'];
            $this->status = $data[0]['status'];
            $this->date = $data[0]['date'];
            $this->products = json_decode($data[0]['products']);
            $files = DBFunctions::select(array('grand_lims_files'),
                                         array('id', 'filename', 'type'),
                                         array('opportunity_id' => $this->id));
            foreach($files as $file){
                $file['data'] = '';
                $file['url'] = "{$wgServer}{$wgScriptPath}/index.php?action=api.limsopportunity/{$this->id}/files/{$file['id']}";
                $this->files[] = $file;
            }
        }
    }

    function getId(){
        return $this->id;
    }

    function getContact(){
        return LIMSContact::newFromId($this->contact);
    }

    function getPerson(){
        return Person::newFromId($this->owner);
    }

    function getOwner(){
        return $this->owner;
    }
    
    function getProject(){
        return Project::newFromId($this->project);
    }
    
    function getProjectId(){
        return $this->project;
    }

    function getUserType(){
        return $this->userType;
    }

    function getDescription(){
        return $this->description;
    }

    function getCategory(){
        return $this->category;
    }
    
    function getSurveyed(){
        return $this->surveyed;
    }
    
    function getResponded(){
        return $this->responded;
    }
    
    function getSatisfaction(){
        return $this->satisfaction;
    }
    
    function getStatus(){
        return $this->status;
    }

    function getDate(){
        return $this->date;
    }

    function getTasks(){
        return LIMSTask::getTasks($this->getId());
    }
    
    function getProducts(){
        return $this->products;
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
        return LIMSContact::isAllowedToCreate();
    }

    function toArray(){
        if($this->isAllowedToView()){
            self::generateRequestId();
            $person = $this->getPerson();
            $proj = $this->getProject();
            $owner = array('id' => $person->getId(),
                           'name' => $person->getNameForForms(),
                           'url' => $person->getUrl());
            $project = array('id' => '', 'name' => '', 'url' => '');
            if($proj != null){
                $project = array('id' => $proj->getId(),
                                 'name' => $proj->getName(),
                                 'url' => $proj->getUrl());
            }
            $json = array('id' => $this->getId(),
                          'requestId' => self::$requestIds[$this->getId()],
                          'contact' => $this->getContact()->getId(),
                          'owner' => $owner,
                          'project' => $project,
                          'userType' => $this->getUserType(),
                          'description' => $this->getDescription(),
                          'category' => $this->getCategory(),
                          'surveyed' => $this->getSurveyed(),
                          'responded' => $this->getResponded(),
                          'satisfaction' => $this->getSatisfaction(),
                          'status' => $this->getStatus(),
                          'products' => $this->getProducts(),
                          'files' => $this->getFiles(),
                          'date' => $this->getDate(),
                          'isAllowedToEdit' => $this->isAllowedToEdit());
            return $json;
        }
        return array();
    }

    function create(){
        if(self::isAllowedToCreate()){
            DBFunctions::insert('grand_lims_opportunity',
                                array('contact' => $this->contact,
                                      'owner' => $this->owner,
                                      'project' => $this->project,
                                      'user_type' => $this->userType,
                                      'description' => $this->description,
                                      'category' => $this->category,
                                      'surveyed' => $this->surveyed,
                                      'responded' => $this->responded,
                                      'satisfaction' => $this->satisfaction,
                                      'status' => $this->status,
                                      'products' => json_encode($this->products),
                                      'date' => COL('CURRENT_TIMESTAMP')));
            $this->id = DBFunctions::insertId();
            $this->uploadFiles();
            self::$requestIds = null;
        }
    }

    function update(){
        if($this->isAllowedToEdit()){
            $newProducts = array();
            foreach($this->products as $product){
                if(!(isset($product->delete) && $product->delete == true)){
                    $newProducts[] = $product;
                }
            }
            $this->products = $newProducts;
            DBFunctions::update('grand_lims_opportunity',
                                array('contact' => $this->contact,
                                      'owner' => $this->owner,
                                      'project' => $this->project,
                                      'user_type' => $this->userType,
                                      'description' => $this->description,
                                      'category' => $this->category,
                                      'surveyed' => $this->surveyed,
                                      'responded' => $this->responded,
                                      'satisfaction' => $this->satisfaction,
                                      'status' => $this->status,
                                      'products' => json_encode($this->products)),
                                array('id' => $this->id));
            $this->uploadFiles();
            self::$requestIds = null;
        }
    }

    function delete(){
        if($this->isAllowedToEdit()){
            DBFunctions::delete('grand_lims_opportunity',
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
