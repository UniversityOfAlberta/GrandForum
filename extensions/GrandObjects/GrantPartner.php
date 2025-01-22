<?php

/**
 * @package GrandObjects
 */

class GrantPartner extends BackboneModel {
    
    var $id;
    var $award_id;
    var $part_institution;
    var $province;
    var $country;
    var $committee_name;
    var $fiscal_year;
    var $org_type;
    
    // Creates a new Partner from the given id
    static function newFromId($id){
        $data = DBFunctions::select(array('grand_new_grant_partner'),
                                    array('*'),
                                    array('id' => EQ($id)));
        $partner = new GrantPartner($data);
        return $partner;
    }
    
    // Creates a new Parter based on the given DB resultset
    function __construct($data){
        if(count($data) > 0){
            $this->id = $data[0]['id'];
            $this->award_id = $data[0]['award_id'];
            $this->part_institution = $data[0]['part_institution'];
            $this->province = $data[0]['province'];
            $this->country = $data[0]['country'];
            $this->committee_name = $data[0]['committee_name'];
            $this->fiscal_year = $data[0]['fiscal_year'];
            $this->org_type = $data[0]['org_type'];
        }
    }
    
    function create(){
        DBFunctions::insert('grand_new_grant_partner',
                            array('award_id' => $this->award_id,
                                  'part_institution' => $this->part_institution,
                                  'province' => $this->province,
                                  'country' => $this->country,
                                  'committee_name' => $this->committee_name,
                                  'fiscal_year' => $this->fiscal_year,
                                  'org_type' => $this->org_type));
        $this->id = DBFunctions::insertId();
        return $this;
    }
    
    function update(){
        DBFunctions::update('grand_new_grant_partner',
                            array('award_id' => $this->award_id,
                                  'part_institution' => $this->part_institution,
                                  'province' => $this->province,
                                  'country' => $this->country,
                                  'committee_name' => $this->committee_name,
                                  'fiscal_year' => $this->fiscal_year,
                                  'org_type' => $this->org_type),
                            array('id' => EQ($this->id)));
        return $this;
    }
    
    function delete(){
        DBFunctions::delete('grand_new_grant_partner',
                            array('id' => EQ($this->id)));
        $this->id = null;
        return $this;
    }
    
    function toArray(){
        $array = array(
            'id' => $this->id,
            'award_id' => $this->award_id,
            'part_institution' => $this->part_institution,
            'province' => $this->province,
            'country' => $this->country,
            'committee_name' => $this->committee_name,
            'fiscal_year' => $this->fiscal_year,
            'org_type' => $this->org_type
        );
        return $array;
    }
    
    function exists(){
        return ($this->id != null && $this->id != 0);
    }
    
    function getCacheId(){
        return "grandPartner{$this->id}";
    }
}
?>
