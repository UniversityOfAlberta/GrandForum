<?php

class Address {

    var $id;
    var $person;
    var $type;
    var $line1;
    var $line2;
    var $line3;
    var $line4;
    var $line5;
    var $city;
    var $province;
    var $country;
    var $code;
    var $start_date;
    var $end_date;
    var $primary;

    /**
     * Returns a new Address from the given id
     * @param integer $id The id of the entry in the DB
     * @return Address The Address from the given id
     */
    static function newFromId($id){
        $data = DBFunctions::select(array('grand_user_addresses'),
                                    array('*'),
                                    array('id' => EQ($id)));
        return new Address($data);
    }
    
    function __construct($data){
        if(count($data) > 0){
            $this->id = $data[0]['id'];
            $this->person = Person::newFromId($data[0]['user_id']);
            $this->type = $data[0]['type'];
            $this->line1 = $data[0]['line1'];
            $this->line2 = $data[0]['line2'];
            $this->line3 = $data[0]['line3'];
            $this->line4 = $data[0]['line4'];
            $this->line5 = $data[0]['line5'];
            $this->city = $data[0]['city'];
            $this->province = $data[0]['province'];
            $this->country = $data[0]['country'];
            $this->code = $data[0]['code'];
            $this->start_date = $data[0]['start_date'];
            $this->end_date = $data[0]['end_date'];
            $this->primary = $data[0]['primary_indicator'];
        }
    }
    
    /**
     * Returns the id of this Address
     * @return integer The id of this Address
     */
    function getId(){
        return $this->id;   
    }
    
    /**
     * Returns the Person that this Address is for
     * @return Person The Person that this Address is for
     */
    function getPerson(){
        return $this->person;
    }
    
    /**
     * Returns the type of Address this is
     * @return string The type of Address this is
     */
    function getType(){
        return $this->type;
    }
    
    /**
     * Returns the first line for this Address
     * @return string The first line for this Address
     */
    function getLine1(){
        return $this->line1;
    }
    
    /**
     * Returns the second line for this Address
     * @return string The second line for this Address
     */
    function getLine2(){
        return $this->line2;
    }
    
    /**
     * Returns the third line for this Address
     * @return string The third line for this Address
     */
    function getLine3(){
        return $this->line3;
    }
    
    /**
     * Returns the fourth line for this Address
     * @return string The fourth line for this Address
     */
    function getLine4(){
        return $this->line4;
    }
    
    /**
     * Returns the fifth line for this Address
     * @return string The fifth line for this Address
     */
    function getLine5(){
        return $this->line5;
    }
    
    /**
     * Returns the name of the city that this Address is in
     * @return string The name of the city that this Address is in
     */
    function getCity(){
        return $this->city;
    }
    
    /**
     * Returns the name of the province (or subdivision) that this Address is in
     * @return string The name of the province (or subdivision) that this Address is in
     */
    function getProvince(){
        return $this->province;
    }
    
    /**
     * Returns the name of the country that this Address is in
     * @return string The name of the country that this Address is in
     */
    function getCountry(){
        return $this->country;
    }
    
    /**
     * Returns the postal/zip code for this Address
     * @return string The postal/zip code for this Address
     */
    function getPostalCode(){
        return $this->code;
    }
    
    /**
     * Returns the start date for this Address
     * @return string The start date for this Address
     */
    function getStartDate(){
        return ($this->start_date == ZOTT) ? "" : $this->start_date;
    }
    
    /**
     * Returns the end date for this address
     * @return string The end date for this address
     */
    function getEndDate(){
        return ($this->end_date == ZOTT) ? "" : $this->end_date;
    }
    
    /**
     * Returns whether or not this is Address is a primary indicator or not
     * @return boolean Whether or not this is Address is a primary indicator or not
     */
    function isPrimary(){
        return $this->primary;
    }

}

?>
