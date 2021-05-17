<?php

class EventRegistration extends BackboneModel {

    var $id;
    var $eventId;
    var $email;
    var $name;
    var $role;
    var $receiveInformation;
    var $joinNewsletter;
    var $createProfile;
    var $similarEvents;

    static function getAllEventRegistrations(){
        $data = DBFunctions::select(array('grand_event_registration'),
                                    array('*'));
        $feeds = array();
        foreach($data as $row){
            $feeds[] = new EventRegistration(array($row));
        }
        return $feeds;
    }
    
    static function newFromId($id){
        $data = DBFunctions::select(array('grand_event_registration'),
                                    array('*'),
                                    array('id' => EQ($id)));
        return new EventRegistration($data);
    }

    function EventRegistration($data=null){
        if(count($data) > 0){
            $this->id = $data[0]['id'];
            $this->eventId = $data[0]['event_id'];
            $this->email = $data[0]['email'];
            $this->name = $data[0]['name'];
            $this->role = $data[0]['role'];
            $this->receiveInformation = $data[0]['receive_information'];
            $this->joinNewsletter = $data[0]['join_newsletter'];
            $this->createProfile = $data[0]['create_profile'];
            $this->similarEvents = $data[0]['similar_events'];
        }
    }
    
    function toArray(){
        return array('id' => $this->id,
                     'event_id' => $this->eventId,
                     'email' => $this->email,
                     'name' => $this->name,
                     'role' => $this->role,
                     'receive_information' => $this->receiveInformation,
                     'join_newsletter' => $this->joinNewsletter,
                     'create_profile' => $this->createProfile,
                     'similar_events' => $this->similarEvents);
    }
    
    function create(){
        DBFunctions::insert('grand_event_registration',
                            array('event_id' => $this->eventId,
                                  'email' => $this->email,
                                  'name' => $this->name,
                                  'role' => $this->role,
                                  'receive_information' => $this->receiveInformation,
                                  'join_newsletter' => $this->joinNewsletter,
                                  'create_profile' => $this->createProfile,
                                  'similar_events' => $this->similarEvents));
        $this->id = DBFunctions::insertId();
        DBFunctions::commit();
    }
    
    function update(){
        DBFunctions::update('grand_event_registration',
                            array('event_id' => $this->eventId,
                                  'email' => $this->email,
                                  'name' => $this->name,
                                  'role' => $this->role,
                                  'receive_information' => $this->receiveInformation,
                                  'join_newsletter' => $this->joinNewsletter,
                                  'create_profile' => $this->createProfile,
                                  'similar_events' => $this->similarEvents),
                            array('id' => EQ($this->id)));
        DBFunctions::commit();
    }
    
    function delete(){
        DBFunctions::delete('grand_event_registration',
                            array('id' => EQ($this->id)));
        DBFunctions::commit();
    }
    
    function exists(){
        return ($this->id > 0);
    }
    
    function getCacheId(){
        return "";
    }

}

?>
