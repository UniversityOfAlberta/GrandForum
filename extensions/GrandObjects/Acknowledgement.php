<?php

class Acknowledgement {
    
    static $cache = array();
    
    var $id;
    var $person;
    var $name;
    var $university;
    var $date;
    var $supervisor;
    var $md5;
    var $pdf;
    var $uploaded;
    
    // Returns the Acknowledgement with the specified $id
    static function newFromId($id){
        if(isset(self::$cache[$id])){
            return self::$cache[$id];
        }
        $sql = "SELECT `id`, `user_id`, `user_name`, `university`, `date`, `supervisor`, `md5`, `uploaded`
                FROM `grand_acknowledgements`
                WHERE id = '".addslashes($id)."'
                LIMIT 1";
        $data = DBFunctions::execSQL($sql);
        $ack = new Acknowledgement($data);
        self::$cache[$ack->id] = &$ack;
        self::$cache[$ack->md5] = &$ack;
        return $ack;
    }
    
    // Returns the Acknowledgement with the specified $md5
    static function newFromMd5($md5){
        if(isset(self::$cache[$md5])){
            return self::$cache[$md5];
        }
        $sql = "SELECT `id`, `user_id`, `user_name`, `university`, `date`, `supervisor`, `md5`, `uploaded`
                FROM `grand_acknowledgements`
                WHERE md5 = '".addslashes($md5)."'
                LIMIT 1";
        $data = DBFunctions::execSQL($sql);
        $ack = new Acknowledgement($data);
        self::$cache[$ack->id] = &$ack;
        self::$cache[$ack->md5] = &$ack;
        return $ack;
    }
    
    static function getAllAcknowledgements(){
        $sql = "SELECT `id`
                FROM `grand_acknowledgements`";
        $data = DBFunctions::execSQL($sql);
        $acks = array();
        foreach($data as $row){
            $acks[] = Acknowledgement::newFromId($row['id']);
        }
        return $acks;
    }
    
    function Acknowledgement($data){
        if(count($data) == 1){
            $this->id = $data[0]['id'];
            $this->person = Person::newFromId($data[0]['user_id']);
            if($this->person == null || $this->person->getName() == ""){
                $this->name = $data[0]['user_name'];
                $this->person = Person::newFromNameLike($this->name);
            }
            if(!($this->person == null || $this->person->getName() == "")){
                $this->name = $this->person->getName();
            }
            $this->university = $data[0]['university'];
            $this->date = $data[0]['date'];
            $this->supervisor = $data[0]['supervisor'];
            $this->md5 = $data[0]['md5'];
            $this->pdf = "";
            $this->uploaded = $data[0]['uploaded'];
        }
    }
    
    // Returns the Person this Acknowledgement is for
    function getPerson(){
        return $this->person;
    }
    
    function getName(){
        return $this->name;
    }
    
    // Returns the Supervisor this Acknowledgement is for
    function getSupervisor(){
        return $this->supervisor;
    }
    
    // Returns the Date this Acknowledgement is for
    function getDate(){
        if(preg_match("/[a-zA-Z]/", $this->date) == 1){
            $date = DateTime::createFromFormat('d-M-y', $this->date);
        }
        else{
            $date = DateTime::createFromFormat('d-m-y', $this->date);
            if($date == false){
                $date = DateTime::createFromFormat('d-m-Y', $this->date);
            }
        }
        
        if($date != false){
            return $date->format('Y-m-d');
        }
        else{
            return "";
        }
    }
    
    // Returns the University this Acknowledgement is for
    function getUniversity(){
        return $this->university;
    }
    
    // Returns the md5 of this Acknowledgement
    function getMd5(){
        return $this->md5;
    }
    
    function getUrl(){
        global $wgServer, $wgScriptPath;
        return "$wgServer$wgScriptPath/index.php?action=getack&ack={$this->md5}";
    }
    
    function getPDF(){
        if($this->pdf == ""){
            $sql = "SELECT `pdf`
                    FROM `grand_acknowledgements`
                    WHERE id = '{$this->id}'";
            $data = DBFunctions::execSQL($sql);
            if(count($data) > 0){
                $this->pdf = $data[0]['pdf'];
            }
        }
        return $this->pdf;
    }
}

?>
