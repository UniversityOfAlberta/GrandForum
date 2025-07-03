<?php

class Telephone {
    
    var $id;
    var $person;
    var $type;
    var $country_code;
    var $area_code;
    var $number;
    var $extension;
    var $start_date;
    var $end_date;
    var $primary;
    
    static function newFromId($id){
        $data = DBFunctions::select(array('grand_user_telephone'),
                                    array('*'),
                                    array('id' => EQ($id)));
        return new Telephone($data);
    }
    
    function __construct($data) {
        if(count($data) > 0){
            $this->id = $data[0]['id'];
            $this->person = Person::newFromId($data[0]['user_id']);
            $this->type = $data[0]['type'];
            $this->country_code = $data[0]['country_code'];
            $this->area_code = $data[0]['area_code'];
            $this->number = $data[0]['number'];
            $this->extension = $data[0]['extension'];
            $this->start_date = $data[0]['start_date'];
            $this->end_date = $data[0]['end_date'];
            $this->primary = $data[0]['primary_indicator'];
        }
    }
    
    /**
     * Returns the id of the Telephone
     * @return integer The id of the Telephone
     */
    function getId(){
        return $this->id;
    }
    
    /**
     * Returns the Person that this Telephone belongs to
     * @return Person The Person that this Telephone belongs to
     */
    function getPerson(){
        return $this->person;
    }
    
    /**
     * Returns the type of Telephone this is
     * @return string The type of Telephone this is
     */
    function getType(){
        return $this->type;
    }
    
    /**
     * Returns the country code of this Telephone
     * @return string The country code of this Telephone
     */
    function getCountryCode(){
        return $this->country_code;
    }
    
    /**
     * Returns the area code of this Telephone
     * @return string The area code of this Telephone
     */
    function getAreaCode(){
        return $this->area_code;
    }
    
    /**
     * Returns the phone number of this Telephone
     * @return string The phone number of this Telephone
     */
    function getPhoneNumber(){
        return $this->number;
    }
    
    /**
     * Returns the extension of this Telephone
     * @return string The extension of this Telephone
     */
    function getExtension(){
        return $this->extension;
    }
    
    /**
     * Returns the start date of this Telephone
     * @return string The start date of this Telephone
     */
    function getStartDate(){
        return ($this->start_date == "0000-00-00 00:00:00") ? "" : $this->start_date;
    }
    
    /**
     * Returns the end date of this Telephone
     * @return string The end date of this Telephone
     */
    function getEndDate(){
        return ($this->end_date == "0000-00-00 00:00:00") ? "" : $this->end_date;
    }
    
    /**
     * Return whether this Telephone is a primary indicator or not
     * @return boolean Whether this Telephone is a primary indicator or not
     */
    function isPrimary(){
        return $this->primary;
    }
}

?>
