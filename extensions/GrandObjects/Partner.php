<?php

/**
 * @package GrandObjects
 */

class Partner {
    
    var $id;
    var $organization;
    var $type;
    var $city;
    var $prov;
    var $country;
    
    // Creates a new Partner from the given id
    static function newFromId($id){
        $data = DBFunctions::select(array('grand_partners'),
                                    array('*'),
                                    array('id' => EQ($id)));
        $partner = new Partner($data);
        return $partner;
    }
    
    // Creates a new Partner from the given name.
    // Since the organization column is not unique, this may return an unexpected result.
    static function newFromName($name){
        $data = DBFunctions::select(array('grand_partners'),
                                    array('*'),
                                    array('organization' => EQ($name)));
        $partner = new Partner($data);
        return $partner;
    }
    
    // Returns an array of all Partners
    static function getAllPartners(){
        $data = DBFunctions::select(array('grand_partners'),
                                    array('*'),
                                    array(),
                                    array('organization' => 'ASC'));
        $partners = array();
        foreach($data as $row){
            $partners[] = new Partner(array($row));
        }
        return $partners;
    }
    
    // Creates a new Parter based on the given DB resultset
    function Partner($data){
        if(count($data) > 0){
            $this->id = $data[0]['id'];
            $this->organization = $data[0]['organization'];
            $this->type = $data[0]['type'];
            $this->city = $data[0]['city'];
            $this->prov = $data[0]['prov_or_state'];
            $this->country = $data[0]['country'];
        }
    }
    
    // Returns the id of this Partner
    function getId(){
        return $this->id;
    }
    
    // Returns the organization name of this Partner
    function getOrganization(){
        return $this->organization;
    }
    
    // Returns the type of this Partner
    function getType(){
        return $this->type;
    }
    
    // Returns the city of this Partner
    function getCity(){
        return $this->city;
    }
    
    // Returns the provice or state for this Partner
    function getProv(){
        return $this->prov;
    }
    
    // Returns the country for this Partner
    function getCountry(){
        return $this->country;
    }
}
?>
