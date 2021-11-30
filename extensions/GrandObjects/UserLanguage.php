<?php

class UserLanguage {

    var $id;
    var $person;
    var $language;
    var $read;
    var $write;
    var $speak;
    var $understand;
    var $review;

    /**
     * Returns a new UserLanguage from the given id
     * @param integer $id The id of the entry in the DB
     * @return UserLanguage The UserLanguage from the given id
     */
    static function newFromId($id){
        $data = DBFunctions::select(array('grand_user_languages'),
                                    array('*'),
                                    array('id' => EQ($id)));
        return new UserLanguage($data);
    }

    function __construct($data){
        if(count($data) > 0){
            $this->id = $data[0]['id'];
            $this->person = Person::newFromId($data[0]['user_id']);
            $this->language = $data[0]['language'];
            $this->read = $data[0]['can_read'];
            $this->write = $data[0]['can_write'];
            $this->speak = $data[0]['can_speak'];
            $this->understand = $data[0]['can_understand'];
            $this->review = $data[0]['can_review'];
        }
    }
    
    /**
     * Returns the id of this UserLanguage
     * @return integer The id of this UserLanguage
     */
    function getId(){
        return $this->id;
    }
    
    /**
     * Returns the Person that this UserLanguage definition belongs to
     * @return Person The Person that this UserLanguage definition belongs to
     */
    function getPerson(){
        return $this->person;
    }
    
    /**
     * Returns the name of this UserLanguage
     * @return string The name of this UserLanguage
     */
    function getLanguage(){
        return $this->language;
    }
    
    /**
     * Returns whether or not the Person can read this UserLanguage
     * @return boolean Whether or not the Person can read this UserLanguage
     */
    function canRead(){
        return $this->read;
    }
    
    /**
     * Returns whether or not the Person can write this UserLanguage
     * @return boolean Whether or not the Person can write this UserLanguage
     */
    function canWrite(){
        return $this->write;
    }
    
    /**
     * Returns whether or not the Person can speak this UserLanguage
     * @return boolean Whether or not the Person can speak this UserLanguage
     */
    function canSpeak(){
        return $this->speak;
    }
    
    /**
     * Returns whether or not the Person can understand this UserLanguage
     * @return boolean Whether or not the Person can understand this UserLanguage
     */
    function canUnderstand(){
        return $this->understand;
    }
    
    /**
     * Returns whether or not the Person can peer review in this UserLanguage
     * @return boolean Whether or not the Person can peer review in this UserLanguage
     */
    function canReview(){
        return $this->review;
    }

}

?>
