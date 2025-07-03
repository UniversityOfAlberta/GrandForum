<?php

/**
 * @package GrandObjects
 */

class AskAnExpertEvent extends BackboneModel {
    
    static $cache = array();
    
    //from API stuff
    var $id;
    var $name_of_expert;
    var $expert_field;
    var $date_of_event;
    var $end_of_event;
    var $active;
    var $date_created;
    var $currently_on;
    var $zoomlink;
    var $date_for_questions;
    var $host;
    var $event;
    var $theme;
    var $desc;
    var $details;
    var $location;

    static function newFromId($id){
        if(isset($cache[$id])){
            return $cache[$id];
        }
        $data = DBFunctions::select(array('grand_avoid_expert_event'),
                                    array('*'),
                                    array('`id`' => EQ($id),
                                          ));
        $askanexpert = new AskAnExpertEvent($data);
        $cache[$id] = $askanexpert;
        return $askanexpert;
    }
    
    static function newFromName($name){
        if(isset($cache[$name])){
            return $cache[$name];
        }
        $data = DBFunctions::select(array('grand_avoid_expert_event'),
                                    array('*'),
                                    array('`name_of_expert`' => EQ($id),
                                          ));
        $askanexpert = new AskAnExpertEvent($data);
        $cache[$name] = $askanexpert;
        return $askanexpert;
    }
    
    static function getAllExpertEvents(){
        $sql = "SELECT * FROM `grand_avoid_expert_event`";
        $data = DBFunctions::execSQL($sql);
        $unis = array();
        foreach($data as $row){
            $unis[] = AskAnExpertEvent::newFromId($row['id']);
        }
        return $unis;

    }

    function __construct($data){
        if(count($data) > 0){
            $row = $data[0];
            $this->id = $row['id'];
            $this->name_of_expert = $row['name_of_expert'];
            $this->expert_field = $row["expert_field"];
            $this->date_of_event = $row["date_of_event"];
            $this->end_of_event = $row["end_of_event"];
            $this->active = $row['active'];
            $this->date_created = $row['date_created'];
            $this->currently_on = $row['currently_on'];
            $this->zoomlink = $row['zoomlink'];
            $this->date_for_questions = $row["date_for_questions"];
            $this->host = $row["host"];
            $this->theme = $row["theme"];
            $this->desc = $row["description"];
            $this->event = $row["event"];
            $this->details = $row["details"];
            $this->location = $row["location"];

        }
    }
    
    function toArray(){
        global $wgUser;
        $json = array(
                    'id' => $this->getId(),
                    'name_of_expert' => $this->name_of_expert,
                    'expert_field' => $this->expert_field,
                    'date_of_event' => $this->date_of_event,
                    'active' => ($this->active == 1),
                    'date_created' => $this->date_created,
                    'end_of_event' => $this->end_of_event,
                    'currently_on' => $this->currently_on,
                    'zoomlink' => $this->zoomlink,
                    'date_for_questions' => $this->date_for_questions,
                    'event' => $this->event,
                    'theme' => $this->theme,
                    'description' => $this->desc,
                    'host' => $this->host,
                    'details' => $this->details,
                    'location' => $this->location,
                );
        return $json;
    }
    
    function create(){
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(STAFF)){
            DBFunctions::begin();
            $status = DBFunctions::insert('grand_avoid_expert_event',
                                          array('name_of_expert' => $this->name_of_expert,
                                                'expert_field' => $this->expert_field,
                                                'date_of_event' => $this->date_of_event,
                                                'active' => $this->active,
                                                'date_created' => $this->date_created,
                                                'end_of_event' => $this->end_of_event,
                                                'currently_on' => $this->currently_on,
                                                'zoomlink' => $this->zoomlink,
                                                'date_for_questions' => $this->date_for_questions,
                                                'event' => $this->event,
                                                'theme' => $this->theme,
                                                'description' => $this->desc,
                                                'host' => $this->host,
                                                'details' => $this->details,
                                                'location' => $this->location,
                                          ), true);
            if($status){
                DBFunctions::commit();
                return true;
            }
        }
        return false; 
    }
    
    function update(){
        $me = Person::newFromWgUser();
        if($me->isRoleAtLeast(STAFF)){
            DBFunctions::begin();
            $status = DBFunctions::update('grand_avoid_expert_event',
                                          array('name_of_expert' => $this->name_of_expert,
                                                'expert_field' => $this->expert_field,
                                                'date_of_event' => $this->date_of_event,
                                                'end_of_event' => $this->end_of_event,
                                                'active' => $this->active,
                                                'date_created' => $this->date_created,
                                                'currently_on' => $this->currently_on,
                                                'zoomlink' => $this->zoomlink,
                                                'date_for_questions' => $this->date_for_questions,
                                                'event' => $this->event,
                                                'theme' => $this->theme,
                                                'description' => $this->desc,
                                                'host' => $this->host,
                                                'details' => $this->details,
                                                'location' => $this->location,
                                          ),
                                          array('id' => EQ($this->id)),
                                          array(),
                                          true);
           if($status){
               DBFunctions::commit();
               return true;
           }
       }
       return false;
    }
    
    function delete(){
        return false;
    }
    
    function exists(){
        return true;
    }
    
    function getCacheId(){
        global $wgSitename;
    }
    
    function getId(){
        return $this->id;
    }
}

?>
