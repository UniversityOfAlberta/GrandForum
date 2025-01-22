<?php

class GrantAward extends BackboneModel {
    
    static $partnersCache = array();
    
    var $id;
    var $user_id;
    var $grant_id;
    var $department;
    var $institution;
    var $province;
    var $country;
    var $start_year;
    var $end_year;
    var $competition_year;
    var $amount;
    var $program_id;
    var $program_name;
    var $group;
    var $committee_name;
    var $area_of_application_group;
    var $area_of_application;
    var $research_subject_group;
    //var $installment;
    //var $partie;
    //var $nb_partie;
    var $application_title;
    var $keyword;
    var $application_summary;
    var $coapplicants;
    var $partners = null;
    var $coapplicantsWaiting;
    
    static function newFromId($id){
        $data = DBFunctions::select(array('grand_new_grants'),
                                    array('*'),
                                    array('id' => EQ($id)));
        $grant = new GrantAward($data);
        return $grant;
    }
    
    static function newFromTitle($title){
        $data = DBFunctions::select(array('grand_new_grants'),
                                    array('*'),
                                    array('application_title' => EQ($title)));
        $grant = new GrantAward($data);
        return $grant;
    }
    
    static function getAllGrantAwards($start=0, $count=999999999, $person=null){
        $me = Person::newFromWgUser();
        $grants = array();
        $where = array();
        if(!$me->isRoleAtLeast(STAFF) || $person != null){
            if($person == null){
                $person = $me;
            }
            $where = array('user_id' => $person->getId(),
                           WHERE_OR('coapplicants') => LIKE("%\"{$person->getId()}\";%"));
        }
        $data = DBFunctions::select(array('grand_new_grants'),
                                    array('*'),
                                    $where,
                                    array(),
                                    array($start, $count));
        foreach($data as $row){
            $grant = new GrantAward(array($row));
            if($grant != null && $grant->getId() != 0){
                $grants[] = $grant;
            }
        }
        return $grants;
    }
    
    static function generatePartnersCache(){
        if(count(self::$partnersCache) == 0){
            $data = DBFunctions::select(array('grand_new_grant_partner'),
                                        array('*'));
            foreach($data as $row){
                self::$partnersCache[$row['award_id']][] = $row;
            }
        }
    }
    
    function __construct($data){
        $me = Person::newFromWgUser();
        if(count($data) > 0){
            $row = $data[0];
            if($me->getId() == $row['user_id'] || strstr($row['coapplicants'], "\"{$me->getId()}\";") !== false || $me->isRoleAtLeast(STAFF)){
                $this->id = $row['id'];
                $this->user_id = $row['user_id'];
                $this->grant_id = $row['grant_id'];
                $this->department = $row['department'];
                $this->institution = $row['institution'];
                $this->province = $row['province'];
                $this->country = $row['country'];
                $this->start_year = $row['start_year'];
                $this->end_year = $row['end_year'];
                $this->competition_year = $row['competition_year'];
                $this->amount = $row['amount'];
                $this->program_id = $row['program_id'];
                $this->program_name = $row['program_name'];
                $this->group = $row['group'];
                $this->committee_name = $row['committee_name'];
                $this->area_of_application_group = $row['area_of_application_group'];
                $this->area_of_application = $row['area_of_application'];
                $this->research_subject_group = $row['research_subject_group'];
                //$this->installment = $row['installment'];
                //$this->partie = $row['partie'];
                //$this->nb_partie = $row['nb_partie'];
                $this->application_title = $row['application_title'];
                $this->keyword = $row['keyword'];
                $this->application_summary = $row['application_summary'];
                $this->coapplicants = $row['coapplicants'];
                $this->coapplicantsWaiting = true;
            }
        }
    }
    
    function getPartners(){
        self::generatePartnersCache();
        if($this->partners == null){
            $this->partners = array();
            if(isset(self::$partnersCache[$this->getId()])){
                $data = self::$partnersCache[$this->getId()];
                $this->partners = array();
                foreach($data as $row){
                    $this->partners[] = new GrantPartner(array($row));
                }
            }
        }
        return $this->partners;
    }
    
    function getCoApplicants(){
        if($this->coapplicantsWaiting){
            $coapplicants = array();
            $unserialized = array();
            if(is_array($this->coapplicants)){
                // For creation/update of Product
                foreach($this->coapplicants as $co){
                    if(isset($co->id)){
                        $unserialized[] = $co->id;
                    }
                    else if(isset($co->fullname)){
                        $unserialized[] = $co->fullname;
                    }
                    else{
                        $unserialized[] = $co->name;
                    }
                }
            }
            else{
                $unserialized = unserialize($this->coapplicants);
                if($unserialized == null){
                    $unserialized = array();
                }
            }
            foreach(@$unserialized as $co){
                if($co == ""){
                    continue;
                }
                $person = null;
                if(is_numeric($co)){
                    $person = Person::newFromId($co);
                }
                else{
                    $person = Person::newFromNameLike($co);
                    if($person == null || $person->getName() == null || $person->getName() == ""){
                        // The name might not match exactly what is in the db, try aliases
                        try{
                            $person = Person::newFromAlias($co);
                        }
                        catch(DomainException $e){
                            $person = null;
                        }
                    }
                }
                Product::generateIllegalAuthorsCache();
                if($person == null || 
                   $person->getName() == null || 
                   $person->getName() == "" || 
                   isset(Product::$illegalAuthorsCache[$person->getNameForForms()]) ||
                   isset(Product::$illegalAuthorsCache[$person->getId()])){
                    // Ok this person is not in the db, make a fake Person object
                    $pdata = array();
                    $pdata[0]['user_id'] = "";
                    $pdata[0]['user_name'] = $co;
                    $pdata[0]['user_real_name'] = $co;
                    $pdata[0]['first_name'] = "";
                    $pdata[0]['middle_name'] = "";
                    $pdata[0]['last_name'] = "";
                    $pdata[0]['prev_first_name'] = "";
                    $pdata[0]['prev_last_name'] = "";
                    $pdata[0]['honorific'] = "";
                    $pdata[0]['language'] = "";
                    $pdata[0]['user_email'] = "";
                    $pdata[0]['user_twitter'] = "";
                    $pdata[0]['user_website'] = "";
                    $pdata[0]['user_registration'] = "";
                    $pdata[0]['user_public_profile'] = "";
                    $pdata[0]['user_private_profile'] = "";
                    $person = new LimitedPerson($pdata);
                    Person::$cache[$co] = $person;
                }
                if($person->getName() == "WikiSysop"){
                    // Under no circumstances should WikiSysop be an author
                    continue;
                }
                $coapplicants[] = $person;
            }
            $this->coapplicants = $coapplicants;
            $this->coapplicantsWaiting = false;
        }
        return $this->coapplicants;
    }
   
    function getUrl(){
        global $wgServer, $wgScriptPath;
        return "$wgServer$wgScriptPath/index.php/Special:GrantAwardPage#/{$this->getId()}";
    }
    
    function create(){
        $coapplicants = array();
        if(!is_array($this->coapplicants)){
            $this->coapplicants = array();
        }
        foreach($this->coapplicants as $co){
            if(isset($co->id) && $co->id != 0){
                $coapplicants[] = $co->id;
            }
            else if(isset($co->fullname)){
                $coapplicants[] = $co->fullname;
            }
            else{
                // This is more for legacy purposes
                $coapplicants[] = $co->name;
            }
        }
        DBFunctions::insert('grand_new_grants',
                            array('user_id' => $this->user_id,
                                  'grant_id' => $this->grant_id,
                                  'department' => $this->department,
                                  'institution' => $this->institution,
                                  'province' => $this->province,
                                  'country' => $this->country,
                                  'start_year' => $this->start_year,
                                  'end_year' => $this->end_year,
                                  'competition_year' => $this->competition_year,
                                  'amount' => str_replace(",", "", $this->amount),
                                  'program_id' => $this->program_id,
                                  'program_name' => $this->program_name,
                                  '`group`' => $this->group,
                                  'committee_name' => $this->committee_name,
                                  'area_of_application_group' => $this->area_of_application_group,
                                  'area_of_application' => $this->area_of_application,
                                  'research_subject_group' => $this->research_subject_group,
                                  //'installment' => $this->installment,
                                  //'partie' => $this->partie,
                                  //'nb_partie' => $this->nb_partie,
                                  'application_title' => $this->application_title,
                                  'keyword' => $this->keyword,
                                  'application_summary' => $this->application_summary,
                                  'coapplicants' => serialize($coapplicants)));
        $this->id = DBFunctions::insertId();
        if(!is_array($this->partners)){
            $this->getPartners();
        }
        foreach($this->partners as $partner){
            if(is_object($partner)){
                $partner = get_object_vars($partner);
            }
            $partner = new GrantPartner(array($partner));
            $partner->award_id = $this->id;
            $partner->create();
        }
        DBFunctions::commit();
        self::$partnersCache = array();
        $this->coapplicantsWaiting = true;
        return $this;
    }
    
    function update(){
        $coapplicants = array();
        if(!is_array($this->coapplicants)){
            $this->getCoApplicants();
        }
        foreach($this->coapplicants as $co){
            if(isset($co->id) && $co->id != 0){
                $coapplicants[] = $co->id;
            }
            else if(isset($co->fullname)){
                $coapplicants[] = $co->fullname;
            }
            else{
                // This is more for legacy purposes
                $coapplicants[] = $co->name;
            }
        }
        $status = DBFunctions::update('grand_new_grants',
                            array('user_id' => $this->user_id,
                                  'grant_id' => $this->grant_id,
                                  'department' => $this->department,
                                  'institution' => $this->institution,
                                  'province' => $this->province,
                                  'country' => $this->country,
                                  'start_year' => $this->start_year,
                                  'end_year' => $this->end_year,
                                  'competition_year' => $this->competition_year,
                                  'amount' => str_replace(",", "", $this->amount),
                                  'program_id' => $this->program_id,
                                  'program_name' => $this->program_name,
                                  '`group`' => $this->group,
                                  'committee_name' => $this->committee_name,
                                  'area_of_application_group' => $this->area_of_application_group,
                                  'area_of_application' => $this->area_of_application,
                                  'research_subject_group' => $this->research_subject_group,
                                  //'installment' => $this->installment,
                                  //'partie' => $this->partie,
                                  //'nb_partie' => $this->nb_partie,
                                  'application_title' => $this->application_title,
                                  'keyword' => $this->keyword,
                                  'application_summary' => $this->application_summary,
                                  'coapplicants' => serialize($coapplicants)),
                            array('id' => EQ($this->id)));
        if(!is_array($this->partners)){
            $this->getPartners();
        }
        DBFunctions::delete('grand_new_grant_partner',
                            array('award_id' => $this->id));
        foreach($this->partners as $partner){
            if(is_object($partner)){
                $partner = get_object_vars($partner);
            }
            $partner = new GrantPartner(array($partner));
            $partner->award_id = $this->id;
            $partner->create();
        }
        DBFunctions::commit();
        self::$partnersCache = array();
        $this->coapplicantsWaiting = true;
        return $this;
    }
    
    function delete(){
        DBFunctions::delete('grand_new_grants',
                            array('id' => EQ($this->id)));
        DBFunctions::delete('grand_new_grant_partner',
                            array('award_id' => $this->id));
        DBFunctions::commit();
        self::$partnersCache = array();
        $this->id = null;
        return $this;
    }
    
    function toArray(){
        $partners = new Collection($this->getPartners());
        $coapplicants = array();
        foreach($this->getCoApplicants() as $co){
            $coapplicants[] = array('id' => $co->getId(),
                                    'name' => $co->getNameForProduct(),
                                    'fullname' => $co->getNameForForms(),
                                    'url' => $co->getUrl());
        }
        $json = array('id' => $this->id,
                      'user_id' => $this->user_id,
                      'grant_id' => $this->grant_id,
                      'department' => $this->department,
                      'institution' => $this->institution,
                      'province' => $this->province,
                      'country' => $this->country,
                      'start_year' => $this->start_year,
                      'end_year' => $this->end_year,
                      'competition_year' => $this->competition_year,
                      'amount' => str_replace(",", "", $this->amount),
                      'program_id' => $this->program_id,
                      'program_name' => $this->program_name,
                      'group' => $this->group,
                      'committee_name' => $this->committee_name,
                      'area_of_application_group' => $this->area_of_application_group,
                      'area_of_application' => $this->area_of_application,
                      'research_subject_group' => $this->research_subject_group,
                      //'installment' => $this->installment,
                      //'partie' => $this->partie,
                      //'nb_partie' => $this->nb_partie,
                      'application_title' => $this->application_title,
                      'keyword' => $this->keyword,
                      'application_summary' => $this->application_summary,
                      'coapplicants' => $this->coapplicants,
                      'url' => $this->getUrl(),
                      'partners' => $partners->toArray(),
                      'coapplicants' => $coapplicants
        );
        return $json;
    }
    
    function exists(){
        return ($this->id != 0);
    }
    
    function getCacheId(){
        return 'grantaward'.$this->getId();
    }

}

?>
