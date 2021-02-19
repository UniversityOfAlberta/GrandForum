<?php

/**
 * @package GrandObjects
 */

class BSIPosting extends Posting {
    
    static $dbTable = 'grand_bsi_postings';

    var $type;
    var $partnerName;
    var $city;
    var $province;
    var $country;
    var $firstName;
    var $lastName;
    var $email;
    var $positions;
    var $positionsText;
    var $discipline;
    var $about;
    var $skills;
    var $deletedText;
    
    function BSIPosting($data){
        if(count($data) > 0){
            $row = $data[0];
            parent::posting($data);
            $this->type = $row['type'];
            $this->partnerName = $row['partner_name'];
            $this->city = $row['city'];
            $this->province = $row['province'];
            $this->country = $row['country'];
            $this->firstName = $row['first_name'];
            $this->lastName = $row['last_name'];
            $this->email = $row['email'];
            $this->positions = $row['positions'];
            $this->positionsText = $row['positions_text'];
            $this->discipline = $row['discipline'];
            $this->about = $row['about'];
            $this->skills = $row['skills'];
            $this->deletedText = $row['deleted_text'];
        }
    }
    
    function getType(){
        return $this->type;
    }
    
    function getPartnerName(){
        return $this->partnerName;
    }
    
    function getCity(){
        return $this->city;
    }
    
    function getProvince(){
        return $this->province;
    }
    
    function getCountry(){
        return $this->country;
    }
    
    function getFirstName(){
        return $this->firstName;
    }
    
    function getLastName(){
        return $this->lastName;
    }
    
    function getEmail(){
        return $this->email;
    }
    
    function getPositions(){
        return $this->positions;
    }
    
    function getPositionsText(){
        return $this->positionsText;
    }
    
    function getDiscipline(){
        return $this->discipline;
    }
    
    function getAbout(){
        return $this->about;
    }
    
    function getSkills(){
        return $this->skills;
    }
    
    function getDeletedText(){
        return $this->deletedText;
    }
    
    function toArray(){
        $json = parent::toArray();
        $json['type'] = $this->getType();
        $json['partnerName'] = $this->getPartnerName();
        $json['city'] = $this->getCity();
        $json['province'] = $this->getProvince();
        $json['country'] = $this->getCountry();
        $json['firstName'] = $this->getFirstName();
        $json['lastName'] = $this->getLastName();
        $json['email'] = $this->getEmail();
        $json['positions'] = $this->getPositions();
        $json['positionsText'] = $this->getPositionsText();
        $json['discipline'] = $this->getDiscipline();
        $json['about'] = $this->getAbout();
        $json['skills'] = $this->getSkills();
        $json['deletedText'] = $this->getDeletedText();
        return $json;
    }
    
    function create(){
        $status = parent::create();
        if($status){
            $status = DBFunctions::update(self::$dbTable,
                                          array('`type`' => $this->type,
                                                'partner_name' => $this->partnerName,
                                                'city' => $this->city,
                                                'province' => $this->province,
                                                'country' => $this->country,
                                                'first_name' => $this->firstName,
                                                'last_name' => $this->lastName,
                                                'email' => $this->email,
                                                'positions' => $this->positions,
                                                'positions_text' => $this->positionsText,
                                                'discipline' => $this->discipline,
                                                'about' => $this->about,
                                                'skills' => $this->skills,
                                                'deleted_text' => $this->deletedText),
                                          array('id' => $this->id));
        }
        return $status;
    }
    
    function update(){
        $status = parent::update();
        if($status){
            $status = DBFunctions::update(self::$dbTable,
                                          array('`type`' => $this->type,
                                                'partner_name' => $this->partnerName,
                                                'city' => $this->city,
                                                'province' => $this->province,
                                                'country' => $this->country,
                                                'first_name' => $this->firstName,
                                                'last_name' => $this->lastName,
                                                'email' => $this->email,
                                                'positions' => $this->positions,
                                                'positions_text' => $this->positionsText,
                                                'discipline' => $this->discipline,
                                                'about' => $this->about,
                                                'skills' => $this->skills,
                                                'deleted_text' => $this->deletedText),
                                          array('id' => $this->id));
        }
        return $status;
    }
}

?>
