<?php

/**
 * @package GrandObjects
 */

class Alumni extends BackboneModel {

    var $id;
    var $user_id;
    var $recruited = "";
    var $recruited_country = "";
    var $alumni = "";
    var $alumni_country = "";
    var $alumni_sector = "";
    
    /**
     * Returns a new Alumni from the given id
     * @param integer $id The id of the Alumni
     * @return Alumni The Alumni with the given id
     */
    static function newFromId($id){
        $data = DBFunctions::select(array('grand_alumni'),
                                    array('*'),
                                    array('id' => EQ($id)));
        $alumni = new Alumni($data);
        return $alumni;
    }
    
    /**
     * Returns a new Alumni from the given id
     * @param integer $id The id of the Alumni
     * @return Alumni The Alumni with the given id
     */
    static function newFromUserId($user_id){
        $data = DBFunctions::select(array('grand_alumni'),
                                    array('*'),
                                    array('user_id' => EQ($user_id)));
        $alumni = new Alumni($data);
        return $alumni;
    }
    
    static function getAllAlumni(){
        $data = DBFunctions::select(array('grand_alumni'),
                                    array('*'));
        $alumni = array();
        foreach($data as $row){
            $alumni[] = new Alumni(array($row));
        }
        return $alumni;
    }
 
    function __construct($data){
        if(count($data) > 0){
            $this->id = $data[0]['id'];
            $this->user_id = $data[0]['user_id'];
            $this->recruited = $data[0]['recruited'];
            $this->recruited_country = $data[0]['recruited_country'];
            $this->alumni = $data[0]['alumni'];
            $this->alumni_country = $data[0]['alumni_country'];
            $this->alumni_sector = $data[0]['alumni_sector'];
        }
    }
    
    function getId(){
        return $this->id;
    }
    
    function getUserId(){
        return $this->user_id;
    }
    
    function getPerson(){
        return Person::newFromId($this->getUserId());
    }
    
    function getRecruited(){
        return $this->recruited;
    }
    
    function getRecruitedCountry(){
        return $this->recruited_country;
    }
    
    function getAlumni(){
        return $this->alumni;
    }
    
    function getAlumniCountry(){
        return $this->alumni_country;
    }
    
    function getAlumniSector(){
        return $this->alumni_sector;
    }
    
    function create(){
        $me = Person::newFromWgUser();
        if($me->isAllowedToEdit($this->getPerson())){
            DBFunctions::insert('grand_alumni',
                                array('user_id' => $this->user_id,
                                      'recruited' => $this->recruited,
                                      'recruited_country' => $this->recruited_country,
                                      'alumni' => $this->alumni,
                                      'alumni_country' => $this->alumni_country,
                                      'alumni_sector' => $this->alumni_sector));
            $this->id = DBFunctions::insertId();
        }
        return $this;
    }
    
    function update(){
        $me = Person::newFromWgUser();
        if($me->isAllowedToEdit($this->getPerson())){
            DBFunctions::update('grand_alumni',
                                array('user_id' => $this->user_id,
                                      'recruited' => $this->recruited,
                                      'recruited_country' => $this->recruited_country,
                                      'alumni' => $this->alumni,
                                      'alumni_country' => $this->alumni_country,
                                      'alumni_sector' => $this->alumni_sector),
                                array('id' => $this->id));
        }
        return $this;
    }
    
    function delete(){
        $me = Person::newFromWgUser();
        if($me->isAllowedToEdit($this->getPerson())){
            DBFunctions::delete('grand_alumni',
                                array('id' => EQ($this->getId())));
            $this->id = 0;
        }
        return $this;
    }
    
    function toArray(){
        $data = array(
            'id' => $this->getId(),
            'user_id' => $this->getUserId(),
            'recruited' => $this->getRecruited(),
            'recruited_country' => $this->getRecruitedCountry(),
            'alumni' => $this->getAlumni(),
            'alumni_country' => $this->getAlumniCountry(),
            'alumni_sector' => $this->getAlumniSector()
        );
        return $data;
    }
    
    function exists(){
        return ($this->id != "" && $this->id != 0);
    }
    
    function getCacheId(){
        return "";
    }
    
}
