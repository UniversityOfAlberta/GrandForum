<?php

class Grant extends BackboneModel {

    var $id;
    var $user_id;
    var $project_id;
    var $sponsor;
    var $total;
    var $funds_before;
    var $funds_after;
    var $speed_code;
    var $title;
    var $description;
    var $request;
    var $start_date;
    var $end_date;
    
    static function newFromId($id){
        $data = DBFunctions::select(array('grand_grants'),
                                    array('*'),
                                    array('id' => EQ($id)));
        $grant = new Grant($data);
        return $grant;
    }
    
    static function newFromProjectId($projectId){
        $data = DBFunctions::select(array('grand_grants'),
                                    array('*'),
                                    array('project_id' => EQ($projectId)));
        $grant = new Grant($data);
        return $grant;
    }
    
    static function getAllGrants(){
        $grants = array();
        $data = DBFunctions::select(array('grand_grants'),
                                    array('*'));
        foreach($data as $row){
            $grants[] = new Grant(array($row));
        }
        return $grants;
    }
    
    function Grant($data){
        if(count($data) > 0){
            $row = $data[0];
            $this->id = $row['id'];
            $this->user_id = $row['user_id'];
            $this->project_id = $row['project_id'];
            $this->sponsor = $row['sponsor'];
            $this->total = $row['total'];
            $this->funds_before = $row['funds_before'];
            $this->funds_after = $row['funds_after'];
            $this->speed_code = $row['speed_code'];
            $this->title = $row['title'];
            $this->description = $row['description'];
            $this->request = $row['request'];
            $this->start_date = $row['start_date'];
            $this->end_date = $row['end_date'];
        }
    }
    
    function getId(){
        return $this->id;
    }
    
    function getUserId(){
        return $this->user_id;
    }
    
    function getProjectId(){
        return $this->project_id;
    }
    
    function getSponsor(){
        return $this->sponsor;
    }
    
    function getTotal(){
        return $this->total;
    }
    
    function getFundsBefore(){
        return $this->funds_before;
    }
    
    function getFundsAfter(){
        return $this->funds_after;
    }
    
    function getSpeedCode(){
        return $this->speed_code;
    }
    
    function getTitle(){
        return $this->title;
    }
    
    function getDescription(){
        return $this->description;
    }
    
    function getRequest(){
        return $this->request;
    }
    
    function getStartDate(){
        return $this->start_date;
    }
    
    function getEndDate(){
        return $this->end_date;
    }
    
    function getUrl(){
        global $wgServer, $wgScriptPath;
        return "$wgServer$wgScriptPath/index.php/Special:GrantPage#/{$this->getId()}";
    }
    
    function create(){
        DBFunctions::insert('grand_grants',
                            array('user_id' => $this->user_id,
                                  'project_id' => $this->project_id,
                                  'sponsor' => $this->sponsor,
                                  'total' => str_replace(",", "", $this->total),
                                  'funds_before' => str_replace(",", "", $this->funds_before),
                                  'funds_after' => str_replace(",", "", $this->funds_after),
                                  'speed_code' => $this->speed_code,
                                  'title' => $this->title,
                                  'description' => $this->description,
                                  'request' => $this->request,
                                  'start_date' => $this->start_date,
                                  'end_date' => $this->end_date));
        DBFunctions::commit();
        $this->id = DBFunctions::insertId();
        return $this;
    }
    
    function update(){
        DBFunctions::update('grand_grants',
                            array('user_id' => $this->user_id,
                                  'project_id' => $this->project_id,
                                  'sponsor' => $this->sponsor,
                                  'total' => str_replace(",", "", $this->total),
                                  'funds_before' => str_replace(",", "", $this->funds_before),
                                  'funds_after' => str_replace(",", "", $this->funds_after),
                                  'speed_code' => $this->speed_code,
                                  'title' => $this->title,
                                  'description' => $this->description,
                                  'request' => $this->request,
                                  'start_date' => $this->start_date,
                                  'end_date' => $this->end_date),
                            array('id' => EQ($this->id)));
        DBFunctions::commit();
        return $this;
    }
    
    function delete(){
        DBFunctions::delete('grand_grants',
                            array('id' => EQ($this->id)));
        DBFunctions::commit();
        $this->id = null;
        return $this;
    }
    
    function toArray(){
        $json = array(
            'id' => $this->id,
            'user_id' => $this->user_id,
            'project_id' => $this->project_id,
            'sponsor' => $this->sponsor,
            'total' => $this->total,
            'funds_before' => $this->funds_before,
            'funds_after' => $this->funds_after,
            'speed_code' => $this->speed_code,
            'title' => $this->title,
            'description' => $this->description,
            'request' => $this->request,
            'start_date' => substr($this->start_date, 0, 10),
            'end_date' => substr($this->end_date, 0, 10),
            'url' => $this->getUrl()
        );
        return $json;
    }
    
    function exists(){
        return ($this->id != 0);
    }
    
    function getCacheId(){
        return 'grant'.$this->getId();
    }

}

?>
