<?php

class EventRegistration extends BackboneModel {

    var $id;
    var $eventId;
    var $email;
    var $name;
    var $role;
    var $webpage;
    var $twitter;
    var $receiveInformation;
    var $joinNewsletter;
    var $createProfile;
    var $similarEvents;
    var $misc;
    var $created;

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
            $this->webpage = $data[0]['webpage'];
            $this->twitter = $data[0]['twitter'];
            $this->receiveInformation = $data[0]['receive_information'];
            $this->joinNewsletter = $data[0]['join_newsletter'];
            $this->createProfile = $data[0]['create_profile'];
            $this->similarEvents = $data[0]['similar_events'];
            $this->misc = json_decode($data[0]['misc']);
            if(!is_array($this->misc) && !is_object($this->misc)){
                $this->misc = array();
            }
            $this->created = $data[0]['created'];
        }
    }
    
    function getEvent(){
        return EventPosting::newFromId($this->eventId);
    }
    
    function toArray(){
        return array('id' => $this->id,
                     'event_id' => $this->eventId,
                     'email' => $this->email,
                     'name' => $this->name,
                     'role' => $this->role,
                     'webpage' => $this->webpage,
                     'twitter' => $this->twitter,
                     'receive_information' => $this->receiveInformation,
                     'join_newsletter' => $this->joinNewsletter,
                     'create_profile' => $this->createProfile,
                     'similar_events' => $this->similarEvents,
                     'misc' => $this->misc,
                     'created' => $this->created);
    }
    
    function create(){
        DBFunctions::insert('grand_event_registration',
                            array('event_id' => $this->eventId,
                                  'email' => $this->email,
                                  'name' => $this->name,
                                  'role' => $this->role,
                                  'webpage' => $this->webpage,
                                  'twitter' => $this->twitter,
                                  'receive_information' => $this->receiveInformation,
                                  'join_newsletter' => $this->joinNewsletter,
                                  'create_profile' => $this->createProfile,
                                  'similar_events' => $this->similarEvents,
                                  'misc' => json_encode($this->misc)));
        $this->id = DBFunctions::insertId();
        DBFunctions::commit();
    }
    
    function update(){
        DBFunctions::update('grand_event_registration',
                            array('event_id' => $this->eventId,
                                  'email' => $this->email,
                                  'name' => $this->name,
                                  'role' => $this->role,
                                  'webpage' => $this->webpage,
                                  'twitter' => $this->twitter,
                                  'receive_information' => $this->receiveInformation,
                                  'join_newsletter' => $this->joinNewsletter,
                                  'create_profile' => $this->createProfile,
                                  'similar_events' => $this->similarEvents,
                                  'misc' => json_encode($this->misc)),
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
